<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\Navigation;

it('batches CP builder hierarchy and linked elements without changing serialized parents', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $section = F::entrySection();
    $children = [];
    foreach (F::entries(20, $section) as $entry) {
        $children[] = F::entryNode($menu, $entry, $parent);
    }
    $children[0]->title = 'Custom child title';
    Craft::$app->getElements()->saveElement($children[0]);
    $dynamic = F::dynamicSectionNode($menu, $section);

    WebRequestSimulator::withAbsoluteUrl('https://builder-perf.invalid/admin/navigation', function() use ($menu, $parent, $children, $dynamic) {
        // URL simulation alone does not set Craft's cached CP-request flag.
        Craft::$app->request->setIsCpRequest(true);
        $previousUser = Craft::$app->getUser();
        // The console app requires its user type; Sites still reads the web
        // session key when the request is CP, even with no active session.
        $user = new class extends craft\console\User {
            public string $idParam = '__id';
        };
        Craft::$app->set('user', $user);
        $user->setIdentity(craft\elements\User::find()->admin()->one());
        try {
            $builder = Navigation::$plugin->getBuilderState();
            $first = $builder->getState($menu->id, $parent->siteId);
            $profile = QueryProfiler::profile(fn() => $builder->getState($menu->id, $parent->siteId));
            expect($profile['queries'])->toBeLessThanOrEqual(8);
            expect($first['nodes'])->toHaveCount(22);
            expect($first['nodes'][21]['id'])->toBe($dynamic->id);
            expect($first['nodes'][21]['hasDescendants'])->toBeFalse();
            expect($first['nodes'][0]['parentId'])->toBeNull();
            expect($first['nodes'][0]['hasDescendants'])->toBeTrue();
            $serializedById = array_column($first['nodes'], null, 'id');

            foreach ($children as $i => $child) {
                $serialized = $serializedById[$child->id];
                expect($serialized['parentId'])->toBe($parent->id);
                expect($serialized['url'])->toBe($child->getUrl());
                expect($serialized['hasDescendants'])->toBeFalse();
                expect($serialized['hasTitleOverride'])->toBe($i === 0);
            }
            fwrite(STDERR, "\nCP builder profile: " . json_encode(array_intersect_key($profile, array_flip(['durationMs', 'queries', 'duplicatePatterns']))) . "\n");
        } finally {
            Craft::$app->set('user', $previousUser);
        }
    });
})->group('perf');
