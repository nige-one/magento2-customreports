<?php declare(strict_types=1);

namespace DEG\CustomReports\Api;

use DEG\CustomReports\Api\Data\AutomatedExportInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Provides logic for executing reports associated to an automated export.
 */
interface ExportReportServiceInterface
{
    /**
     * Export all reports associated to an automated export.
     *
     * Optionally, a pre-built report collection may be provided per custom report id (keyed by id). When present, it is
     * exported as-is instead of querying a fresh, unfiltered collection. This allows the interactive grid export to
     * honour the active grid filters/sort, while automated (cron) exports continue to export the full report.
     *
     * @param AutomatedExportInterface $automatedExport
     * @param AbstractDb[] $reportCollections
     *
     * @return void
     */
    public function exportAll(AutomatedExportInterface $automatedExport, array $reportCollections = []): void;
}
