<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuildSessionsController;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision;
use verbb\navigation\Navigation as N;

use craft\events\DefineRulesEvent;
use craft\services\Structures;

use yii\base\Event;
use yii\base\UserException;
use yii\validators\Validator;

class BuildSessionPassiveParentValidator extends Validator
{
    public static ?int $menuId = null;

    public function validateAttribute($model, $attribute): void
    {
        if (!$model instanceof Node || $model->menuId !== self::$menuId) {
            return;
        }

        $parent = $model->getParent();

        if ($parent && !$parent->isPassive()) {
            $model->addError($attribute, 'Only passive nodes are allowed as parents.');
        }
    }
}

it('returns node validation messages when publishing staged additions', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Pending validation', '/pending-validation');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();

    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId);
    $sessions->stageAddedNodes($session, [$node->id]);
    $message = 'Only passive nodes are allowed as parents.';
    $reject = static function($event) use ($node, $message) {
        if ($event->sender instanceof Node && $event->sender->id === $node->id && !$event->sender->getIsPendingPublish()) {
            $event->sender->addError('parentId', $message);
            $event->isValid = false;
        }
    };

    Event::on(Node::class, Node::EVENT_BEFORE_VALIDATE, $reject);

    try {
        boundaryRequest(function() use ($menu, $node, $message) {
            Craft::$app->getRequest()->setBodyParams([
                'menuId' => $menu->id,
                'siteId' => $node->siteId,
            ]);

            $response = (new BuildSessionsController('build-sessions', N::$plugin))->actionPublish();

            expect($response->statusCode)->toBe(400);
            expect($response->data['message'])->toBe($message);
        });
    } finally {
        Event::off(Node::class, Node::EVENT_BEFORE_VALIDATE, $reject);
    }

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded->getIsPendingPublish())->toBeTrue();
    expect($reloaded->enabled)->toBeFalse();
    expect($sessions->getSession($menu->id, $node->siteId))->not->toBeNull();
});

it('returns user-facing exceptions raised while applying structure moves', function() {
    $menu = F::menu();
    $first = F::customNode($menu, 'First', '/first');
    $second = F::customNode($menu, 'Second', '/second');
    $message = 'Only passive nodes are allowed to have children.';
    $reject = static function($event) use ($second, $message) {
        if ($event->element instanceof Node && $event->element->id === $second->id) {
            throw new UserException($message);
        }
    };

    Event::on(Structures::class, Structures::EVENT_BEFORE_UPDATE_ELEMENT, $reject);

    try {
        boundaryRequest(function() use ($menu, $first, $second, $message) {
            Craft::$app->getRequest()->setBodyParams([
                'menuId' => $menu->id,
                'siteId' => $first->siteId,
                'applyStructure' => true,
                'structureRevision' => BuilderStructureRevision::get($menu),
                'moves' => [
                    ['elementId' => $second->id, 'parentId' => null, 'prevId' => null],
                    ['elementId' => $first->id, 'parentId' => null, 'prevId' => $second->id],
                ],
            ]);

            $response = (new BuildSessionsController('build-sessions', N::$plugin))->actionPublish();

            expect($response->statusCode)->toBe(400);
            expect($response->data['message'])->toBe($message);
        });
    } finally {
        Event::off(Structures::class, Structures::EVENT_BEFORE_UPDATE_ELEMENT, $reject);
    }

    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$first->id, $second->id]);
});

it('runs custom node validation against the proposed structure', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Active parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child');
    $message = 'Only passive nodes are allowed as parents.';
    BuildSessionPassiveParentValidator::$menuId = $menu->id;
    $defineRule = static function(DefineRulesEvent $event) {
        if ($event->sender instanceof Node && $event->sender->menuId === BuildSessionPassiveParentValidator::$menuId) {
            $event->rules[] = [['parentId'], BuildSessionPassiveParentValidator::class];
        }
    };

    Event::on(Node::class, Node::EVENT_DEFINE_RULES, $defineRule);

    try {
        boundaryRequest(function() use ($menu, $parent, $child, $message) {
            Craft::$app->getRequest()->setBodyParams([
                'menuId' => $menu->id,
                'siteId' => $parent->siteId,
                'applyStructure' => true,
                'structureRevision' => BuilderStructureRevision::get($menu),
                'moves' => [
                    ['elementId' => $parent->id, 'parentId' => null, 'prevId' => null],
                    ['elementId' => $child->id, 'parentId' => $parent->id, 'prevId' => null],
                ],
            ]);

            $response = (new BuildSessionsController('build-sessions', N::$plugin))->actionPublish();

            expect($response->statusCode)->toBe(400);
            expect($response->data['message'])->toBe($message);
        });
    } finally {
        Event::off(Node::class, Node::EVENT_DEFINE_RULES, $defineRule);
        BuildSessionPassiveParentValidator::$menuId = null;
    }

    $reloaded = Node::find()->id($child->id)->siteId($child->siteId)->status(null)->one();

    expect($reloaded->getParentId())->toBeNull();
});
