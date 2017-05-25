<?php

namespace Spiral\NavigationBuilder\Database\Sources;

use Spiral\Database\Entities\Database;
use Spiral\NavigationBuilder\Database\Domain;
use Spiral\NavigationBuilder\Database\Link;
use Spiral\NavigationBuilder\Database\Tree;
use Spiral\NavigationBuilder\Database\Types\TreeStatus;
use Spiral\ORM\Entities\RecordSelector;
use Spiral\ORM\Entities\RecordSource;
use Spiral\ORM\ORMInterface;

class TreeSource extends RecordSource
{
    const RECORD = Tree::class;

    /**
     * @param Domain $domain
     * @return RecordSelector
     */
    public function findByDomain(Domain $domain): RecordSelector
    {
        $query = [
            Tree::DOMAIN_ID => $domain->primaryKey()
        ];

        return $this->find($query);
    }

    /**
     * @param Domain $domain
     * @param bool   $publicOnly
     * @return RecordSelector
     */
    public function findDomainTree(Domain $domain, bool $publicOnly = true): RecordSelector
    {
        $query = [
            Tree::DOMAIN_ID => $domain->primaryKey()
        ];

        if (!empty($publicOnly)) {
            $query['status'] = TreeStatus::ACTIVE;
        }

        return $this->find($query)
            ->with('link', ['alias' => 'tree_link'])
            ->load('link', ['using' => 'tree_link']);
    }

    /**
     * @param Domain $domain
     * @param        $linkID
     * @param null   $parentID
     * @return null|Tree
     */
//    public function findOneByDomainAndLink(Domain $domain, $linkID, $parentID = null)
//    {
//        $query = [
//            Tree::LINK_ID   => $linkID,
//            Tree::DOMAIN_ID => $domain->primaryKey(),
//            'status'        => TreeStatus::ACTIVE
//        ];
//
//        if (!empty($parentID)) {
//            $query[Tree::PARENT_ID] = $parentID;
//        }
//
//        return $this->findOne($query);
//    }
}