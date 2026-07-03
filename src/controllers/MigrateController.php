<?php
namespace verbb\navigation\controllers;

use verbb\navigation\migrations\plugins\Line;
use verbb\navigation\migrations\plugins\MigrationResult;
use verbb\navigation\Navigation;

use Craft;
use craft\helpers\App;
use craft\web\Controller;

use Throwable;

use yii\web\NotFoundHttpException;
use yii\web\Response;

class MigrateController extends Controller
{
    // Constants
    // =========================================================================

    private const SESSION_FLASH_OUTPUTS = 'navigation.migrationOutputs';


    // Properties
    // =========================================================================

    protected array|bool|int $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;


    // Public Methods
    // =========================================================================

    public function actionIndex(string $sourceId): Response
    {
        $this->requireAdmin(false);

        $source = $this->_requireSource($sourceId);
        $menus = Navigation::$plugin->getMigrations()->getSourceMenus($sourceId);

        $variables = [
            'sourceId' => $sourceId,
            'source' => $source,
            'menus' => $menus,
            'selectedTab' => 'migrate/' . $sourceId,
            'selectedSubnavItem' => 'settings',
        ];

        $outputs = Craft::$app->getSession()->getFlash(self::SESSION_FLASH_OUTPUTS);

        if (is_array($outputs) && $outputs !== []) {
            $variables['outputs'] = $outputs;
        }

        return $this->renderTemplate('navigation/settings/migrate/index', $variables);
    }

    public function actionRun(?string $sourceId = null): ?Response
    {
        $this->requirePostRequest();
        $this->requireAdmin(false);
        App::maxPowerCaptain();

        $sourceId = $sourceId ?? (string)$this->request->getRequiredBodyParam('sourceId');

        $this->_requireSource($sourceId);

        try {
            Craft::$app->getDb()->backup();
        } catch (Throwable) {
        }

        $menuHandles = $this->request->getBodyParam('menuHandles');
        $skipExisting = (bool)$this->request->getBodyParam('skipExisting');
        $handles = $this->_resolveMenuHandles($sourceId, $menuHandles);

        if ($handles === []) {
            $this->setFailFlash(Craft::t('navigation', 'No menus selected.'));

            return $this->redirectToPostedUrl();
        }

        $migratorClass = Navigation::$plugin->getMigrations()->getMigratorClass($sourceId);
        $outputs = [];
        $hasErrors = false;

        foreach ($handles as $handle) {
            $migration = Navigation::$plugin->createMigrator($migratorClass, [
                'handle' => $handle,
                'skipExisting' => $skipExisting,
            ]);

            try {
                $result = $migration->run();
                $outputs[$handle] = MigrationResult::renderLinesHtml($result->lines);

                if (!$result->ok) {
                    $hasErrors = true;
                }
            } catch (Throwable $e) {
                $hasErrors = true;
                $outputs[$handle] = MigrationResult::renderLinesHtml([
                    Line::error('Failed to migrate: ' . $e->getMessage()),
                ]);
            }
        }

        Craft::$app->getSession()->setFlash(
            self::SESSION_FLASH_OUTPUTS,
            $outputs,
        );

        if ($hasErrors) {
            $this->setFailFlash(Craft::t('navigation', 'Migration completed with errors.'));
        } else {
            $this->setSuccessFlash(Craft::t('navigation', 'Menus migrated.'));
        }

        return $this->redirectToPostedUrl();
    }


    // Private Methods
    // =========================================================================

    private function _requireSource(string $sourceId): array
    {
        $sources = Navigation::$plugin->getMigrations()->getSources();
        $source = $sources[$sourceId] ?? null;

        if (!$source || !($source['ready'] ?? false)) {
            throw new NotFoundHttpException('Migration source not found.');
        }

        return $source;
    }

    private function _resolveMenuHandles(string $sourceId, mixed $menuHandles): array
    {
        if ($menuHandles === '*') {
            return array_column(
                Navigation::$plugin->getMigrations()->getSourceMenus($sourceId),
                'handle',
            );
        }

        if (!is_array($menuHandles)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $menuHandles)));
    }
}
