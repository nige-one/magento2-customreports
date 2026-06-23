<?php declare(strict_types=1);

namespace DEG\CustomReports\Api;

use DEG\CustomReports\Api\Data\CustomReportInterface;
use DEG\CustomReports\Model\GenericReportCollection;

interface CustomReportManagementInterface
{
    /**
     * @param CustomReportInterface $customReport
     * @return GenericReportCollection
     */
    public function getGenericReportCollection(CustomReportInterface $customReport): GenericReportCollection;

    /**
     * @param CustomReportInterface $customReport
     * @param bool $filtersPresent
     * @return string[]
     */
    public function getColumnsList(CustomReportInterface $customReport, bool $filtersPresent): array;

    /**
     * Parse per-column grid types from a "/* Column:type,... *\/" annotation comment in the report SQL.
     *
     * @param CustomReportInterface $customReport
     * @return string[]
     */
    public function getColumnTypes(CustomReportInterface $customReport): array;
}
