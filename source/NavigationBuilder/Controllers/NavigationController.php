<?php

namespace Spiral\NavigationBuilder\Controllers;

use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\NavigationBuilder\NavigationBuilderConfig;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;
use Spiral\NavigationBuilder\Database\Sources\TreeSource;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\Navigation;
use Spiral\NavigationBuilder\Requests\LinkRequest;
use Spiral\NavigationBuilder\Services\Builder;
use Spiral\NavigationBuilder\Services\DomainService;
use Spiral\NavigationBuilder\Services\LinkWrapper;
use Spiral\NavigationBuilder\Services\LinkService;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Views\ViewManager;

/**
 * Class NavigationController
 *
 * @package Spiral\NavigationBuilder\Controllers
 * @property InputManager    $input
 * @property ViewManager     $views
 * @property ResponseWrapper $response
 */
class NavigationController extends Controller
{
    use AuthorizesTrait, TranslatorTrait;

    const GUARD_NAMESPACE = 'vault.navigation';

    /**
     * Builder view page.
     *
     * @param NavigationBuilderConfig $config
     *
     * @return string
     */
    public function indexAction(NavigationBuilderConfig $config)
    {
        return $this->views->render('navigation:vault/list', [
            'domains' => $config->domains()
        ]);
    }

    /**
     * Return all links list.
     *
     * @param LinkService $service
     *
     * @return array
     */
    public function getLinksAction(LinkService $service)
    {
        return [
            'status' => 200,
            'links'  => $service->getList()
        ];
    }

    /**
     * Get domain links tree by its name($id).
     *
     * @param string|int    $id
     * @param Navigation    $navigation
     * @param DomainService $service
     *
     * @return array
     */
    public function domainTreeAction($id, Navigation $navigation, DomainService $service)
    {
        if (!$service->domainExists($id)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No domain found by name "%s".'), $id)
            ];
        }

        return [
            'status' => 200,
            'links'  => $navigation->getTree($id, false)
        ];
    }

    /**
     * Create link.
     *
     * @param LinkRequest $request
     * @param LinkWrapper $wrapper
     * @param LinkSource  $source
     *
     * @return array
     */
    public function createLinkAction(LinkRequest $request, LinkWrapper $wrapper, LinkSource $source)
    {
        $this->allows('add');

        if (!$request->isValid()) {
            return [
                'status' => 400,
                'errors' => $request->getErrors()
            ];
        }

        $link = $source->createFromRequest($request);

        return [
            'status' => 200,
            'link'   => $wrapper->wrapLink($link)
        ];
    }

    /**
     * Delete link by its id.
     *
     * @param string|int  $id
     * @param LinkSource  $source
     * @param LinkService $service
     *
     * @return array
     */
    public function deleteLinkAction($id, LinkSource $source, LinkService $service)
    {
        $this->allows('delete');

        /** @var Link $link */
        $link = $source->findByPK($id);
        if (empty($link)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No link found by id "%s".'), $id)
            ];
        }

        if (!$service->deleteAllowed($link)) {
            return [
                'status' => 400,
                'error'  => $this->say('Can\'t delete, remove from all domain trees first.')
            ];
        }

        $link->delete();

        return [
            'status'  => 200,
            'message' => $this->say('Link deleted.')
        ];
    }

    /**
     * Update link data by its id.
     *
     * @param string|int  $id
     * @param LinkSource  $source
     * @param LinkRequest $request
     * @param LinkWrapper $wrapper
     * @param Navigation  $navigation
     *
     * @return array
     */
    public function updateLinkAction(
        $id,
        LinkSource $source,
        LinkRequest $request,
        LinkWrapper $wrapper,
        Navigation $navigation
    ) {
        $this->allows('update');

        /** @var Link $link */
        $link = $source->findByPK($id);
        if (empty($link)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No link found by id "%s".'), $id)
            ];
        }

        if (!$request->isValid()) {
            return [
                'status' => 400,
                'errors' => $request->getErrors()
            ];
        }

        $link->setFields($request);
        $link->setAttributes($request->getAttributes());
        $link->save();

        /** @var Tree $tree */
        foreach ($link->tree as $tree) {
            $navigation->rebuild($tree->domain);
        }

        return [
            'status' => 200,
            'link'   => $wrapper->wrapLink($link)
        ];
    }

    /**
     * Copy link by its id with new fields.
     *
     * @param string|int  $id
     * @param LinkSource  $source
     * @param LinkWrapper $wrapper
     * @param LinkService $service
     *
     * @return array
     */
    public function copyLinkAction(
        $id,
        LinkSource $source,
        LinkWrapper $wrapper,
        LinkService $service
    ) {
        $this->allows('add');

        /** @var Link $link */
        $link = $source->findByPK($id);
        if (empty($link)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No link found by id "%s".'), $id)
            ];
        }

        $copy = $service->createCopy($link);

        return [
            'status' => 200,
            'link'   => $wrapper->wrapLink($copy)
        ];
    }

    /**
     * Update tree status by its id.
     *
     * @param string|int $id
     * @param TreeSource $source
     *
     * @return array
     */
    public function updateTreeStatusAction($id, TreeSource $source)
    {
        $this->allows('update');

        /** @var Tree $tree */
        $tree = $source->findByPK($id);
        if (empty($tree)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No tree found by id "%s".'), $id)
            ];
        }

        $status = $this->input->data('status');
        if (!$tree->setStatus($status)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('Invalid status "%s" passed.'), $status)
            ];
        }

        $tree->save();

        return [
            'status' => 200,
            'tree'   => [
                'id'     => $id,
                'status' => $tree->status->packValue()
            ]
        ];
    }

    /**
     * Save domain navigation tree.
     *
     * @param string|int    $id
     * @param Builder       $builder
     * @param Navigation    $navigation
     * @param DomainService $service
     *
     * @return array
     */
    public function saveAction(
        $id,
        Builder $builder,
        Navigation $navigation,
        DomainService $service
    ) {
        $this->allows('update');

        if (!$service->domainExists($id)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No domain found by name "%s".'), $id)
            ];
        }

        $builder->saveStructure($id, $this->input->data('tree'));

        return [
            'status' => 200,
            'links'  => $navigation->getTree($id, false)
        ];
    }
}