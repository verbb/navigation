<?php
namespace verbb\navigation\controllers;

use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation;

use Craft;
use craft\helpers\Console;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\Controller;
use craft\web\UploadedFile;

use stdClass;

use yii\helpers\Markdown;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ImportExportController extends Controller
{
    // Static Methods
    // =========================================================================

    private static function _countNodes(array $nodes): int
    {
        $count = 0;

        foreach ($nodes as $node) {
            $count++;
            $count += self::_countNodes($node['children'] ?? []);
        }

        return $count;
    }


    // Public Methods
    // =========================================================================

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requireAdmin(false);

        return true;
    }

    public function actionIndex(?string $importError = null, ?string $exportError = null): Response
    {
        return $this->renderTemplate('navigation/settings/import-export/index', [
            'importError' => $importError,
            'exportError' => $exportError,
            'selectedSubnavItem' => 'settings',
        ]);
    }

    public function actionImport(): ?Response
    {
        $uploadedFile = UploadedFile::getInstanceByName('file');

        if (!$uploadedFile) {
            $this->setFailFlash(Craft::t('navigation', 'An error occurred.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'importError' => Craft::t('navigation', 'You must upload a file.'),
            ]);

            return null;
        }

        $filename = 'navigation-import-' . gmdate('ymd_His') . '.json';

        if (!preg_match(ImportExportHelper::IMPORT_FILENAME_PATTERN, $filename)) {
            throw new BadRequestHttpException('Invalid import filename.');
        }

        $fileLocation = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $filename;
        $uploadedFile->saveAs($fileLocation, false);

        $object = new stdClass();
        $object->filename = $filename;

        return $this->redirectToPostedUrl($object);
    }

    public function actionImportConfigure(string $filename): Response
    {
        $fileLocation = ImportExportHelper::resolveImportFileLocation($filename);

        if (!$fileLocation || !file_exists($fileLocation)) {
            throw new HttpException(404);
        }

        $json = Json::decode(file_get_contents($fileLocation));

        if (isset($json[0]) && is_array($json[0])) {
            $json = $json[0];
        }

        $handle = $json['menu']['handle'] ?? null;
        $existingMenu = $handle ? Navigation::$plugin->getMenus()->getMenuByHandle($handle) : null;

        $menuName = $json['menu']['name'] ?? '';
        $menuHandle = $json['menu']['handle'] ?? '';

        ob_start();
        $this->_stdout("Menu: Preparing to import menu “{$menuName}”.");
        $this->_stdout("    > Menu name is “{$menuName}”.", Console::FG_GREEN);
        $this->_stdout("    > Menu handle is “{$menuHandle}”.", ($existingMenu ? Console::FG_RED : Console::FG_GREEN));

        $nodeCount = self::_countNodes($json['nodes'] ?? []);
        $nodeCountLabel = Craft::t('app', '{num, number} {num, plural, =1{node} other{nodes}}', ['num' => $nodeCount]);
        $this->_stdout("    > Menu contains {$nodeCountLabel}.", Console::FG_GREEN);

        $this->_stdoutNodes($json['nodes'] ?? []);

        if (!empty($json['menuFieldValues'])) {
            $fieldCount = Craft::t('app', '{num, number} {num, plural, =1{menu field value} other{menu field values}}', [
                'num' => count($json['menuFieldValues']),
            ]);
            $this->_stdout("Menu fields: Preparing to import {$fieldCount}.", Console::FG_GREEN);
        }

        $summary = ob_get_clean();

        $variables = compact('filename', 'summary', 'json', 'existingMenu');
        $variables = array_merge($variables, Craft::$app->getUrlManager()->getRouteParams(), [
            'selectedSubnavItem' => 'settings',
        ]);

        return $this->renderTemplate('navigation/settings/import-export/import-configure', $variables);
    }

    public function actionImportComplete(): ?Response
    {
        $filename = (string)$this->request->getParam('filename');
        $menuAction = (string)$this->request->getParam('menuAction', 'create');
        $fileLocation = ImportExportHelper::resolveImportFileLocation($filename);

        if (!$fileLocation || !file_exists($fileLocation)) {
            throw new HttpException(404);
        }

        $payload = Json::decode(file_get_contents($fileLocation));
        $handle = $payload['menu']['handle'] ?? null;
        $existingMenu = $handle ? Navigation::$plugin->getMenus()->getMenuByHandle($handle) : null;

        if ($existingMenu && ($menuAction === '' || !in_array($menuAction, ['create', 'update'], true))) {
            $this->setFailFlash(Craft::t('navigation', 'Choose whether to create a new menu or replace the existing one.'));

            return $this->redirect('navigation/settings/import-export/import-configure/' . $filename);
        }

        if ($menuAction === 'update') {
            if ($existingMenu) {
                $this->requirePermission('navigation-manageMenu:' . $existingMenu->uid);
            }
        } else {
            $this->requirePermission('navigation-createMenus');
        }

        $result = ImportExportHelper::importMenuFromJson(file_get_contents($fileLocation), $menuAction);

        if ($result->hasImportErrors()) {
            $this->setFailFlash(Craft::t('navigation', 'Unable to import menu.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'errors' => $result->errors,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('navigation', 'Menu imported.'));

        return $this->redirectToPostedUrl($result->menu);
    }

    public function actionImportCompleted(?int $menuId): Response
    {
        $menu = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$menu) {
            throw new NotFoundHttpException('Menu not found');
        }

        return $this->renderTemplate('navigation/settings/import-export/import-completed', [
            'menu' => $menu,
            'selectedSubnavItem' => 'settings',
        ]);
    }

    public function actionExport(): void
    {
        $menuId = (int)$this->request->getRequiredParam('menuId');

        if (!$menuId) {
            $this->setFailFlash(Craft::t('navigation', 'An error occurred.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'exportError' => Craft::t('navigation', 'You must select a menu.'),
            ]);

            return;
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$menu) {
            $this->setFailFlash(Craft::t('navigation', 'An error occurred.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'exportError' => Craft::t('navigation', 'Menu not found.'),
            ]);

            return;
        }

        $this->requirePermission('navigation-editMenu:' . $menu->uid);

        $export = ImportExportHelper::generateMenuExport($menu);
        $json = Json::encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Craft::$app->getResponse()->sendContentAsFile($json, 'navigation-' . $menu->handle . '-' . StringHelper::UUID() . '.json');

        Craft::$app->end();
    }


    // Private Methods
    // =========================================================================

    private function _stdout(string $string, string $color = ''): void
    {
        $class = '';

        if ($color) {
            $class = 'color-' . $color;
        }

        echo '<div class="log-label ' . $class . '">' . Markdown::processParagraph($string) . '</div>';
    }

    private function _stdoutNodes(array $nodes, int $depth = 2): void
    {
        $prefix = str_repeat('    ', $depth);

        foreach ($nodes as $node) {
            $type = Html::encode($node['type'] ?? 'unknown');
            $title = Html::encode($node['title'] ?? '');

            $this->_stdout("{$prefix}> {$type}: “{$title}”.", Console::FG_GREEN);
            $this->_stdoutNodes($node['children'] ?? [], $depth + 1);
        }
    }
}
