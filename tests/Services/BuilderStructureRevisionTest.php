<?php
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use yii\web\ConflictHttpException;

it('rejects a stale full-tree save without replacing the newer order', function() {
    $menu=F::menu();$a=F::customNode($menu,'A','/a');$b=F::customNode($menu,'B','/b');
    $version=Revision::get($menu);
    Revision::apply($menu,$version,fn()=>Navigation::$plugin->getBuildSessions()->applyStructureMoves($menu,$a->siteId,[
        ['elementId'=>$b->id,'parentId'=>null,'prevId'=>null],
        ['elementId'=>$a->id,'parentId'=>null,'prevId'=>$b->id],
    ]));
    expect(Revision::get($menu))->not->toBe($version);
    expect(fn()=>Revision::apply($menu,$version,fn()=>throw new RuntimeException('Stale callback must not run')))->toThrow(ConflictHttpException::class);
    expect(Node::find()->menuId($menu->id)->ids())->toBe([$b->id,$a->id]);
    expect(fn()=>Revision::apply($menu,null,fn()=>null))->toThrow(ConflictHttpException::class);
    expect(Revision::apply($menu,Revision::get($menu),fn()=>true))->toBeTrue();
});
