<?php

namespace Openl10n\Bundle\CoreBundle\Processor;

use Openl10n\Bundle\CoreBundle\Action\ExportDomainAction;
use Openl10n\Bundle\CoreBundle\Object\Locale;
use Openl10n\Bundle\CoreBundle\Object\Slug;
use Openl10n\Bundle\CoreBundle\Repository\DomainRepositoryInterface;
use Openl10n\Bundle\CoreBundle\Repository\TranslationRepositoryInterface;
use Openl10n\Bundle\CoreBundle\Translation\TranslationDumperInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Translation\MessageCatalogue;

class ExportDomainProcessor
{
    protected $domainRepository;
    protected $translationRepository;
    protected $translationDumper;

    public function __construct(
        DomainRepositoryInterface $domainRepository,
        TranslationRepositoryInterface $translationRepository,
        TranslationDumperInterface $translationDumper
    )
    {
        $this->domainRepository = $domainRepository;
        $this->translationRepository = $translationRepository;
        $this->translationDumper = $translationDumper;
    }

    public function execute(ExportDomainAction $action)
    {
        $project = $action->getProject();

        $locale = new Locale($action->locale);
        $domainSlug = new Slug($action->domain);
        $format = $action->format;
        $options = $action->options;

        $domain = $this->domainRepository->findOneBySlug($project, $domainSlug);
        if (null === $domain) {
            throw new \Exception('Err, domain not found');
        }

        // Export messages into a MessageCatalogue
        $messages = $this->translationRepository->export($domain, $locale);
        $catalogue = new MessageCatalogue($locale, array($domain->getSlug()->toString() => $messages));

        // Dump to file
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'export_'.mt_rand(0, 99999);
        $this->translationDumper->dumpMessages($catalogue, $directory, $format);
        $filename = $domainSlug.'.'.$locale.'.'.$format;
        $filepath = $directory.DIRECTORY_SEPARATOR.$filename;

        return new File($filepath);
    }
}
