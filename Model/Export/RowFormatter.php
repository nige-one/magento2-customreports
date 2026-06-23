<?php declare(strict_types=1);

namespace DEG\CustomReports\Model\Export;

use DEG\CustomReports\Api\Data\CustomReportInterface;
use DEG\CustomReports\Api\CustomReportManagementInterface;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;

class RowFormatter
{
    private LayoutInterface $layout;
    private CustomReportManagementInterface $customReportManagement;

    public function __construct(
        LayoutInterface $layout,
        CustomReportManagementInterface $customReportManagement
    ) {
        $this->layout = $layout;
        $this->customReportManagement = $customReportManagement;
    }

    /**
     * @param CustomReportInterface $customReport
     * @return Column[]
     */
    public function getColumns(CustomReportInterface $customReport): array
    {
        $types = $this->customReportManagement->getColumnTypes($customReport);

        $columns = [];
        foreach ($this->customReportManagement->getColumnsList($customReport) as $name) {
            $column = $this->layout->createBlock(Column::class);
            $column->setData('index', $name);
            $column->setData('type', $types[$name] ?? 'text');
            $columns[$name] = $column;
        }

        return $columns;
    }

    /**
     * @param DataObject $row
     * @param Column[] $columns
     * @return array
     * @throws LocalizedException
     */
    public function format(DataObject $row, array $columns): array
    {
        $data = $row->getData();
        foreach ($columns as $name => $column) {
            if (!array_key_exists($name, $data)) {
                continue;
            }
            try {
                $data[$name] = $column->getRowFieldExport($row);
            } catch (\Throwable $e) {
                throw new LocalizedException(
                    __('Cast type "%1" is not available for column "%2".', (string)$column->getType(), $name),
                    $e instanceof \Exception ? $e : null
                );
            }
        }

        return $data;
    }
}
