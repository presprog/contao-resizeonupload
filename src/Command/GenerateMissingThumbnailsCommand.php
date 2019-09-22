<?php
/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Zinke <bz@presentprogressive.de>
 */

declare(strict_types=1);

namespace PresProg\ContaoResizeOnUploadBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use PresProg\ContaoResizeOnUploadBundle\ImageResizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateMissingThumbnailsCommand
 * @package PresProg\ContaoResizeOnUploadBundle\Command
 */
class GenerateMissingThumbnailsCommand extends ContainerAwareCommand
{
    /** @var ImageResizer $imageResizer */
    private $imageResizer = null;

    /** @var ContaoFrameworkInterface */
    private $framework;

    /** @var FilesModel $filesModel */
    private $filesModel;

    /**
     * GenerateMissingThumbnailsCommand constructor.
     * @param ContaoFrameworkInterface $framework
     * @param ImageResizer $imageResizer
     */
    public function __construct(ContaoFrameworkInterface $framework, ImageResizer $imageResizer)
    {
        $this->framework = $framework;
        $this->imageResizer = $imageResizer;

        $this->framework->initialize();

        /** @var FilesModel $filesModel */
        $this->filesModel = $this->framework->getAdapter(FilesModel::class);

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('presprog:thumbs:generate')
            ->setDescription('Generates missing thumbs, that are in a (sub) folder with pre-defined image sizes.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function run(InputInterface $input, OutputInterface $output): void
    {
        // Overwrite default logger to stream logs to the console
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $logger = new ConsoleLogger($output);
        $this->imageResizer->setLogger($logger);

        // Get all folders with pre-defined image sizes
        $objFolders = $this->imageResizer->getAllFoldersWithImageSizes();

        if ($objFolders === null) {
            $output->writeln('No folders with pre-defined image sizes.');
            return;
        }

        $processedFiles = [];

        foreach ($objFolders as $objFolder) {
            $processedFiles = $this->generateThumbsForFolder($objFolder, $processedFiles);
        }

        $output->writeln("Processed " . count($processedFiles) . " files.");
    }

    /**
     * @param FilesModel $objFolder
     * @param array $processedFiles
     */
    private function generateThumbsForFolder(FilesModel $objFolder, array $processedFiles = []): array
    {
        // Fetch all files and subfolders by given path
        $objFiles = $this->filesModel->findMultipleByBasePath($objFolder->path . '/');

        if ($objFiles === null) {
            return $processedFiles;
        }

        // Iterate over files and subfolders
        foreach ($objFiles as $objFile) {

            // Skip folders, because files in subfolders are already in $objFiles
            if ($objFile->type === 'folder') {
                continue;
            }

            // File was already processed
            if ($processedFiles[$objFile->id]) {
                continue;
            }

            // Only insert folder path here
            $sizes = $this->imageResizer->getImageSizesForFolder(dirname($objFile->path));

            // No sizes for this file
            if (empty($sizes)) {
                continue;
            }

            $this->imageResizer->resizeImage($objFile, $sizes);
            $processedFiles[$objFile->id] = $objFile->path;
        }

        return $processedFiles;
    }
}