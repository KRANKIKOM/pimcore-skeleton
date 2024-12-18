<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration as AbstractPimcoreMigration;
use Pimcore\Model\Document;

class Version20241218112658 extends AbstractPimcoreMigration
{
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     * @throws \Exception
     */
    public function up(Schema $schema): void
    {
        $this->write("Getting root document");
        $root = Document\Page::getByPath('/');

        if (!$root) {
            $this->write("Root document not found");
            return;
        }

        $this->write("Creating includes folder");
        $includesFolder = new Document\Folder();
        $includesFolder->setParentId($root->getId());
        $includesFolder->setKey('includes');
        $includesFolder->save();

        $this->write("Creating footer snippet");
        $snippedFooter = new Document\Snippet();
        $snippedFooter->setParentId($includesFolder->getId());
        $snippedFooter->setKey('footer');
        $snippedFooter->setTemplate('@PimcoreJetpakk/includes/footer.html.twig');
        $snippedFooter->setController('Krankikom\PimcoreJetpakkBundle\Controller\DefaultController::defaultAction');
        $snippedFooter->save();

        $this->write("Creating header snippet");
        $snippedHeader = new Document\Snippet();
        $snippedHeader->setParentId($includesFolder->getId());
        $snippedHeader->setKey('header');
        $snippedHeader->setTemplate('@PimcoreJetpakk/includes/header.html.twig');
        $snippedHeader->setController('Krankikom\PimcoreJetpakkBundle\Controller\DefaultController::defaultAction');
        $snippedHeader->save();

        $this->write("Setting footer and header properties on root document");
        $root->setProperty('footer', 'document', $snippedFooter, false, true);
        $root->setProperty('header', 'document', $snippedHeader, false, true);
        $root->save();
    }

    /**
     * @param Schema $schema
     * @throws \Exception
     */
    public function down(Schema $schema): void
    {
        // Remove properties from root document
        $root = Document\Page::getByPath('/');
        if (!$root) {
            $this->write("Root document not found");
            return;
        }

        $root->removeProperty('footer');
        $root->removeProperty('header');
        $root->save();

        // Delete header and footer snippets
        $headerSnippet = Document\Snippet::getByPath('/includes/header');
        if ($headerSnippet) {
            $headerSnippet->delete();
        }

        $footerSnippet = Document\Snippet::getByPath('/includes/footer');
        if ($footerSnippet) {
            $footerSnippet->delete();
        }

        // Delete includes folder
        $includesFolder = Document\Folder::getByPath('/includes');
        if ($includesFolder) {
            $includesFolder->delete();
        }
    }
}
