<?php declare(strict_types=1);

namespace DEG\CustomReports\Controller\Adminhtml\CustomReport;

use DEG\CustomReports\Api\AutomatedExportManagementInterface;
use DEG\CustomReports\Api\Data\AutomatedExportInterfaceFactory;
use DEG\CustomReports\Api\ExportReportServiceInterface;
use DEG\CustomReports\Block\Adminhtml\Report\Grid;
use DEG\CustomReports\Model\Config\Source\ExportTypes;
use DEG\CustomReports\Model\CustomReport;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class Export extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'DEG_CustomReports::customreport_export_report';

    public function __construct(
        Context $context,
        protected FileFactory $fileFactory,
        protected Builder $builder,
        protected ExportReportServiceInterface $exportReportService,
        protected AutomatedExportInterfaceFactory $automatedExportFactory,
        protected AutomatedExportManagementInterface $automatedExportManagement
    ) {
        parent::__construct($context);
    }

    /**
     * Export data to file in the format provided by filetype. Leverages automated export functionality.
     * See \DEG\CustomReports\Model\Config\Source\FileTypes for valid 'filetype' param values.
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $fileType = $this->getRequest()->getParam('filetype');
        $customReport = $this->builder->build($this->getRequest());

        $adhocAutomatedExport = $this->automatedExportFactory->create();
        $adhocAutomatedExport->setCustomreportIds([$customReport->getId()]);
        $adhocAutomatedExport->setFilenamePattern(AutomatedExportManagementInterface::VARIABLE_REPORTNAME);
        $adhocAutomatedExport->setExportTypes([ExportTypes::LOCAL_FILE_DROP]);
        $adhocAutomatedExport->setFileTypes([$fileType]);

        $this->exportReportService->exportAll(
            $adhocAutomatedExport,
            $this->getFilteredReportCollections($customReport)
        );

        return $this->fileFactory->create(
            $this->automatedExportManagement->getFilename($adhocAutomatedExport, $customReport, $fileType),
            [
                'type' => 'filename',
                'value' => $this->automatedExportManagement->getAbsoluteLocalFilepath(
                    $adhocAutomatedExport,
                    $customReport,
                    $fileType
                ),
                'rm' => true,
            ],
            DirectoryList::VAR_DIR
        );
    }

    /**
     * Build the report grid from the report layout handle and return its prepared collection, keyed by report id, so
     * the export honours the active grid filters and sort order. The grid block applies the request filter/sort to the
     * collection in exactly the same way as the on-screen report. Returns an empty array (full, unfiltered export) when
     * the report or grid block cannot be resolved.
     *
     * @param CustomReport $customReport
     * @return AbstractDb[]
     */
    private function getFilteredReportCollections(CustomReport $customReport): array
    {
        if (!$customReport->getId()) {
            return [];
        }

        $this->_view->loadLayout(['default', 'deg_customreports_customreport_report']);
        $grid = $this->_view->getLayout()->getBlock('deg_customreports_report_grid');
        if (!$grid instanceof Grid) {
            return [];
        }

        $collection = $grid->getPreparedCollection();
        $collection->setPageSize(0);

        return [$customReport->getId() => $collection];
    }
}
