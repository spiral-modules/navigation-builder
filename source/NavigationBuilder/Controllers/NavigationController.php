<?php

namespace Spiral\NavigationBuilder\Controllers;

use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\NavigationBuilder\Database\Domain;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Sources\DomainSource;
use Spiral\NavigationBuilder\Database\Sources\LinkSource;
use Spiral\NavigationBuilder\Database\Sources\TreeSource;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\DomainNavigation;
use Spiral\NavigationBuilder\Requests\LinkRequest;
use Spiral\NavigationBuilder\Services\Builder;
use Spiral\NavigationBuilder\Services\LinkWrapper;
use Spiral\NavigationBuilder\Services\VaultService;
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
     * @param VaultService $service
     * @return string
     */
    public function indexAction(VaultService $service)
    {
        return $this->views->render('navigation:vault/list', [
            'domains' => $service->getDomainsList()
        ]);
    }

    /**
     * Return all links list.
     *
     * @param VaultService $service
     * @return array
     */
    public function getLinksAction(VaultService $service)
    {
        return [
            'status' => 200,
            'links'  => $service->getLinksList()
        ];
    }

    /**
     * Create domain by given name.
     *
     * @param DomainSource $source
     * @return array
     */
    public function createDomainAction(DomainSource $source)
    {
        $this->allows('add');

        $name = $this->input->data('name');
        $domain = $source->findByName($name);

        if (!empty($domain)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('Domain name "%s" is already taken.'), $name)
            ];
        }

        return [
            'status' => 200,
            'domain' => [
                'id'   => $domain->primaryKey(),
                'name' => $domain->name
            ]
        ];
    }

    /**
     * Delete domain by given id.
     *
     * @param string|int       $id
     * @param DomainSource     $source
     * @param Builder          $builder
     * @param DomainNavigation $navigation
     * @return array
     */
    public function deleteDomainAction(
        $id,
        DomainSource $source,
        Builder $builder,
        DomainNavigation $navigation
    ) {
        $this->allows('delete');

        /** @var Domain $domain */
        $domain = $source->findByPK($id);
        if (empty($domain)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No domain found by id "%s".'), $id)
            ];
        }

        $builder->deleteDomainTree($domain);
        $navigation->dropDomainCache($domain->name);
        $domain->delete();

        return [
            'status'  => 200,
            'message' => $this->say('Link deleted.')
        ];
    }

    /**
     * Get domain links tree by its name($id).
     *
     * @param string|int       $id
     * @param DomainNavigation $navigation
     * @param DomainSource     $source
     * @return array
     */
    public function domainTreeAction($id, DomainNavigation $navigation, DomainSource $source)
    {
        $domain = $source->findByName($id);
        if (empty($domain)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No domain found by name "%s".'), $id)
            ];
        }

        $cache = $this->input->input('no-cache', false) ? false : true;

        return [
            'status' => 200,
            'links'  => $navigation->getTree($domain, $cache)
        ];
    }

    /**
     * Create link.
     *
     * @param LinkRequest $request
     * @param LinkWrapper $wrapper
     * @return array
     */
    public function createLinkAction(LinkRequest $request, LinkWrapper $wrapper)
    {
        $this->allows('add');

        if (!$request->isValid()) {
            return [
                'status' => 400,
                'errors' => $request->getErrors()
            ];
        }

        $link = new Link();
        $link->setFields($request);
        $link->setAttributes($request->getAttributes());
        $link->save();

        return [
            'status' => 200,
            'link'   => $wrapper->wrapLink($link)
        ];
    }

    /**
     * Delete link by its id.
     *
     * @param string|int $id
     * @param LinkSource $source
     * @return array
     */
    public function deleteLinkAction($id, LinkSource $source)
    {
        $this->allows('delete');

        $link = $source->findByPK($id);
        if (empty($link)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No link found by id "%s".'), $id)
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
     * @return array
     */
    public function updateLinkAction(
        $id,
        LinkSource $source,
        LinkRequest $request,
        LinkWrapper $wrapper
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
     * @return array
     */
    public function copyLinkAction(
        $id,
        LinkSource $source,
        LinkWrapper $wrapper
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

        $copy = new Link();
        $copy->text = $link->text;
        $copy->href = $link->href;
        $copy->attributes = $link->attributes;
        $copy->save();

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
     * @param string|int       $id
     * @param DomainSource     $source
     * @param Builder          $builder
     * @param DomainNavigation $navigation
     * @return array
     */
    public function saveAction(
        $id,
        DomainSource $source,
        Builder $builder,
        DomainNavigation $navigation
    ) {
        $this->allows('update');

        $domain = $source->findByName($id);
        if (empty($domain)) {
            return [
                'status' => 400,
                'error'  => sprintf($this->say('No domain found by name "%s".'), $id)
            ];
        }

        $builder->saveStructure($domain, $this->input->data('tree'));

        return [
            'status' => 200,
            'links'  => $navigation->getTree($domain)
        ];
    }
}