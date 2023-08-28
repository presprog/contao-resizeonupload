<?php declare(strict_types=1);

/**
 * @copyright: Copyright (c), Present Progressive GbR
 * @author: Benedict Massolle <bm@presentprogressive.de>
 */

namespace PresProg\ContaoResizeOnUploadBundle\Command;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use PresProg\ContaoResizeOnUploadBundle\ImageResizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMissingThumbnailsCommand extends Command
{
    private ?ImageResizer $imageResizer;

    private ContaoFramework $framework;

    private Adapter $filesModel;

    public function __construct(ContaoFramework $framework, ImageResizer $imageResizer)
    {
        $this->framework    = $framework;
        $this->imageResizer = $imageResizer;

        /** @var FilesModel $filesModel */
        $this->filesModel = $this->framework->getAdapter(FilesModel::class);

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('presprog:thumbs:generate')
            ->setDescription('Generates missing thumbs, that are in a (sub) folder with pre-defined image sizes.');
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        $this->framework->initialize();

        // Overwrite default logger to stream logs to the console
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $logger = new ConsoleLogger($output);
        $this->imageResizer->setLogger($logger);

        // Get all folders with pre-defined image sizes
        /**  @var FilesModel[] $folders */
        $folders = $this->imageResizer->getAllFoldersWithImageSizes();

        if ($folders === null) {
            $output->writeln('No folders with pre-defined image sizes.');
            return;
        }

        $processedFiles = [];

        foreach ($folders as $folder) {
            $processedFiles = $this->generateThumbsForFolder($folder, $processedFiles);
        }

        $output->writeln("Processed " . count($processedFiles) . " files.");
    }

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
