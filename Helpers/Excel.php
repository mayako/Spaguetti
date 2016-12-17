<?php
/**
 * array(
 *     'fill' => array(
 *         'type'  => PHPExcel_Style_Fill::FILL_SOLID,
 *         'color' => array(
 *             'rgb' => 'FFAA55'
 *         )
 *     ),
 *     'font' => array(
 *         'name'      => 'Arial',
 *         'bold'      => true,
 *         'italic'    => true,
 *         'underline' => PHPExcel_Style_Font::UNDERLINE_DOUBLE,
 *         'strike'    => true,
 *         'color'     => array(
 *             'rgb' => 'FFFFFFFF'
 *         ),
 *         'size' => 15
 *     ),
 *     'alignment' => array(
 *         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
 *         'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
 *     ),
 *     'borders' => array(
 *         'allborders' => array(
 *             'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
 *             'color' => array(
 *                 'rgb' => '808080'
 *             )
 *         )
 *     )
 * );
 */


//-------------------------------------------------------------
//------------ WORKBOOK ---------------------------------------
//-------------------------------------------------------------

/**
 * Create a Excel workbook
 * @return PHPExcel
 */
function excel_create()
{
    return new PHPExcel();
}

/**
 * Download Excel file
 * @param  PHPExcel $excel
 * @param  string   $filename
 * @param  string   $format
 */
function excel_download(PHPExcel $excel, $filename, $ext = 'xls')
{
    $formats = array('xls' => 'Excel5', 'xlsx' => 'Excel2007', 'csv' => 'CSV');

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'.'.$ext.'"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($excel, $formats[$ext]);
    $writer->save('php://output');
}


//-------------------------------------------------------------
//------------ WORKSHEETS -------------------------------------
//-------------------------------------------------------------

/**
 * Get a worksheet
 * @param  PHPExcel $excel
 * @param  int|string $index_or_name
 * @return PHPExcel_Worksheet
 */
function excel_sheet(PHPExcel $excel, $index_or_name, array $options = array())
{
    if (excel_sheet_exists($excel, $index_or_name)) {

        $sheet = excel_get_sheet($excel, $index_or_name);

    } else if (is_string($index_or_name)) {

        $sheet = excel_add_sheet($excel, $index_or_name) ;

    } else {

        $sheet = excel_add_sheet($excel, null, $index_or_name);

    }

    if ($options) {
        excel_sheet_options($sheet, $options);
    }

    return $sheet;
}

/**
 * Add a worksheet in the workbook
 * @param  PHPExcel $excel
 * @param  string $name
 * @return PHPExcel_Worksheet
 */
function excel_add_sheet(PHPExcel $excel, $name = null, $index = null)
{
    $worksheet = $excel->createSheet($index);

    if ($name) {
        $worksheet->setTitle($name);
    }

    return $worksheet;
}

/**
 * Get a worksheet
 * @param  PHPExcel $excel
 * @param  int|string   $index_or_name
 * @return PHPExcel_Worksheet
 */
function excel_get_sheet(PHPExcel $excel, $index_or_name)
{
    if (is_string($index_or_name))
    {
        return $excel->getSheetByName($index_or_name);
    }

    return $excel->getSheet($index_or_name);
}

/**
 * Validate if a workseheet exists
 * @param  PHPExcel $excel
 * @param  int|string   $index_or_name
 * @return bool                  s
 */
function excel_sheet_exists(PHPExcel $excel, $index_or_name)
{
    try {
        if (is_string($index_or_name)) {
            return $excel->sheetNameExists($index_or_name);
        }

        $excel->getSheet($index_or_name);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Set Worksheet options
 * @param  PHPExcel_Worksheet $worksheet
 * @param  array              $options
 */
function excel_sheet_options(PHPExcel_Worksheet $worksheet, array $options)
{
    if ($title = array_take($options, 'title')) {
        $worksheet->setTitle($title);
    }
}


//-------------------------------------------------------------
//------------ COLUMNS AND ROWS -------------------------------
//-------------------------------------------------------------

/**
 * Get a column from a worksheet.
 * @param  PHPExcel_Worksheet $worksheet
 * @param  string $column
 * @return PHPExcel_Worksheet_ColumnDimension
 */
function excel_column(PHPExcel_Worksheet $worksheet, $column_index, array $options = array())
{
    $column = $worksheet->getColumnDimension($column_index);

    if ($width = array_take($options, 'width')) {
        $column->setWidth($width);
    }

    if ($autosize = array_take($options, 'autosize')) {
        $column->setAutoSize($autosize);
    }

    if ($style = array_take($options, 'style')) {
        $worksheet->getStyle($column_index)->applyFromArray($style);
    }
}

/**
 * Get a row from a worksheet
 * @param  PHPExcel_Worksheet $worksheet
 * @param  int $row
 * @return PHPExcel_Worksheet_RowDimension
 */
function excel_row(PHPExcel_Worksheet $worksheet, $row_index, array $options = array())
{
    $row = $worksheet->getRowDimension($row_index);

    if ($height = array_take($options, 'height')) {
        $row->setRowHeight($height);
    }

    if ($style = array_take($options, 'style')) {
        $row->getStyle($row_index)->applyFromArray($style);
    }
}

//-------------------------------------------------------------
//------------ CELLS ------------------------------------------
//-------------------------------------------------------------

/**
 * Get a cell from a works
 * @param  PHPExcel_Worksheet $worksheet
 * @param  string $column
 * @param  int $row
 * @return PHPExcel_Cell
 */
function excel_cell(PHPExcel_Worksheet $worksheet, $cell, $value, array $options = array())
{
    list($value, $options) = is_array($value) ? array(null, $value) : array($value, $options);

    if ($value) {
        $worksheet->setCellValue($cell, $value);
    }

    if ($autofilter = array_take($options, 'autofilter')) {
        $worksheet->setAutoFilter($cell);
    }

    if ($style = array_take($options, 'style')) {
        $worksheet->getStyle($cell)->applyFromArray($style);
    }
}

/**
 * Set the styles to a cells range
 * @param  PHPExcel_Worksheet $worksheet
 * @param  string $range
 * @param  array  $format
 * @return PHPExcel_Cell
 */
function excel_range(PHPExcel_Worksheet $worksheet, $range, array $options = array())
{
    if ($autofilter = array_take($options, 'autofilter')) {
        $worksheet->setAutoFilter($range);
    }

    if ($merge = array_take($options, 'merge')) {
        $worksheet->mergeCells($range);
    }

    if ($style = array_take($options, 'style')) {
        $worksheet->getStyle($range)->applyFromArray($style);
    }
}

//-------------------------------------------------------------
//------------ TABLES -----------------------------------------
//-------------------------------------------------------------

/**
 * Print a table from array
 * @param  PHPExcel_Worksheet $worksheet
 * @param  array              $values
 * @param  string             $start_cell
 * @param  array              $options
 */
function excel_table_from_array(PHPExcel_Worksheet $worksheet, array $values, $start_cell = 'A1', array $options = array())
{
    if (!is_multiple($values)) {
        $values = array($values);
    }

    list($start_column, $start_row) = PHPExcel_Cell::coordinateFromString($start_cell);


    foreach ($values as $row_data) {
        $current_column = $start_column;
        foreach($row_data as $cell_value) {
            $worksheet->getCell($current_column . $start_row)->setValue($cell_value);
            ++$current_column;
        }
        ++$start_row;
    }

    $range = $start_cell.':'.(chr(ord($current_column) - 1)).($start_row - 1);

    if ($options) {
        excel_range($worksheet, $range, $options);
    }

    return $range;
}

//-------------------------------------------------------------
//------------ IMAGES -----------------------------------------
//-------------------------------------------------------------

/**
 * Add a image to a worksheet
 * @param  PHPExcel_Worksheet $worksheet
 * @param  string             $cell
 * @param  string             $path
 * @param  array              $options
 */
function excel_image(PHPExcel_Worksheet $worksheet, $cell, $path, array $options = array())
{
    $img = new PHPExcel_Worksheet_Drawing();
    $img->setPath($path);
    $img->setWorksheet($worksheet);
    $img->setCoordinates($cell);
    $img->setResizeProportional(false);

    if (!empty($options['height'])) {
        $img->setHeight($options['height']);
    }

    if (!empty($options['width'])) {
        $img->setWidth($options['width']);
    }
}