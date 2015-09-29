<?PHP
/*
 * $Id: PdfPTable.php,v 1.3 2005/12/19 19:06:32 mstaylor Exp $
 * $Name:  $
 *
 * Copyright 2005 by Mills W. Staylor, III.
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the License.
 *
 * The Original Code is 'iText, a free Java-PDF library'.
 *
 *
 * Alternatively, the contents of this file may be used under the terms of the
 * LGPL license (the "GNU LIBRARY GENERAL PUBLIC LICENSE"), in which case the
 * provisions of LGPL are applicable instead of those above.  If you wish to
 * allow use of your version of this file only under the terms of the LGPL
 * License and not to allow others to use your version of this file under
 * the MPL, indicate your decision by deleting the provisions above and
 * replace them with the notice and other provisions required by the LGPL.
 * If you do not delete the provisions above, a recipient may use your version
 * of this file under either the MPL or the GNU LIBRARY GENERAL PUBLIC LICENSE.
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the MPL as stated above or under the terms of the GNU
 * Library General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Library general Public License for more
 * details.
 */


require_once("../Phrase.php");
require_once("../Element.php");
require_once("../Image.php");
require_once("../Rectangle.php");
require_once("../ElementListener.php");
require_once("../DocumentException.php");
require_once("PdfPCell.php");
require_once("PdfPTableEvent.php");

require_once("PdfWriter.php");
require_once("PdfPCell.php");
require_once("PdfPRow.php");
require_once("PdfContentByte.php");
require_once("../../exceptions/NullPointerException.php");
require_once("../../exceptions/IllegalArgumentException.php");




/** This is a table that can be put at an absolute position but can also
 * be added to the document as the class <CODE>Table</CODE>.
 * In the last case when crossing pages the table always break at full rows; if a
 * row is bigger than the page it is dropped silently to avoid infinite loops.
 * <P>
 * A PdfPTableEvent can be associated to the table to do custom drawing
 * when the table is rendered.
 * @author Paulo Soares (psoares@consiste.pt)
 */

class PdfPTable implements Element
{


    /** The index of the original <CODE>PdfcontentByte</CODE>.
    */
    const BASECANVAS = 0;
    /** The index of the duplicate <CODE>PdfContentByte</CODE> where the background will be drawn.
    */
    const BACKGROUNDCANVAS = 1;
    /** The index of the duplicate <CODE>PdfContentByte</CODE> where the border lines will be drawn.
    */
    const LINECANVAS = 2;
    /** The index of the duplicate <CODE>PdfContentByte</CODE> where the text will be drawn.
     */
    const TEXTCANVAS = 3;

    protected $rows = array();
    protected $totalHeight = 0.0;
    protected $currentRow = NULL;//array of PdfPCell
    protected $currentRowIdx = 0;
    protected PdfPCell defaultCell = new PdfPCell((Phrase)null);
    protected $totalWidth = 0.0;
    protected $relativeWidths = NULL;//array
    protected $absoluteWidths = NULL;//array
    protected $tableEvent = NULL;//PdfPTableEvent

    /** Holds value of property headerRows. */
    protected $headerRows = 0;

    /** Holds value of property widthPercentage. */
    protected $widthPercentage = 80.0;

    /** Holds value of property horizontalAlignment. */
    private $horizontalAlignment = Element::ALIGN_CENTER;

    /** Holds value of property skipFirstHeader. */
    private $skipFirstHeader = FALSE;

    protected $isColspan = FALSE;

    protected $runDirection = PdfWriter::RUN_DIRECTION_DEFAULT;

    /**
     * Holds value of property lockedWidth.
     */
    private $lockedWidth = FALSE;

    /**
    * Holds value of property splitRows.
    */
    private $splitRows = TRUE;

    /** The spacing before the table. */
    protected $spacingBefore = 0.0;

    /** The spacing after the table. */
    protected $spacingAfter = 0.0;

    /**
    * Holds value of property extendLastRow.
    */
    private $extendLastRow = FALSE;

    /**
    * Holds value of property headersInEvent.
    */
    private $headersInEvent = FALSE;

    /**
    * Holds value of property splitLate.
    */
    private $splitLate = TRUE;


    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_array($arg1) == TRUE)
                    construct1argArray($arg1);
                else if (is_integer($arg1) == TRUE)
                    construct1argInteger($arg1);
                break;
            }
        }
    }


    private function construct0args()
    {

    }

    /** Constructs a <CODE>PdfPTable</CODE> with the relative column widths.
    * @param relativeWidths the relative column widths
    */
    private function construct1argArray(array $relativeWidths) {
        if ($relativeWidths == NULL)
            throw new NullPointerException("The widths array in PdfPTable constructor can not be null.");
        if (count($relativeWidths) == 0)
            throw new IllegalArgumentException("The widths array in PdfPTable constructor can not have zero length.");
        $this->relativeWidths = array();
        $this->relativeWidths = array_merge(array(), $relativeWidths);
        $absoluteWidths = array();
        calculateWidths();
        $currentRow = array();
    }


    /** Constructs a <CODE>PdfPTable</CODE> with <CODE>numColumns</CODE> columns.
    * @param numColumns the number of columns
    */
    private function construct1argInteger($numColumns) {
        if ($numColumns <= 0)
            throw new IllegalArgumentException("The number of columns in PdfPTable constructor must be greater than zero.");
        $relativeWidths = array();
        for ($k = 0; $k < $numColumns; ++$k)
            $relativeWidths[$k] = 1;
        $absoluteWidths = array();
        calculateWidths();
        $currentRow = array();
    }

    /** Constructs a copy of a <CODE>PdfPTable</CODE>.
    * @param table the <CODE>PdfPTable</CODE> to be copied
    */
    private function construct1argPdfPTable(PdfPTable $table) {
        copyFormat($table);
        for ($k = 0; k < count($currentRow); ++$k) {
            if ($table->currentRow[$k] == NULL)
                break;
            $currentRow[$k] = new PdfPCell($table->currentRow[$k]);
        }
        for ($k = 0; $k < count($table->rows); ++$k) {
            $row = $table->rows[$k];
            if ($row != NULL)
                $row = new PdfPRow($row);
            array_push($rows, $row);
        }
    }


    /**
    * Makes a shallow copy of a table (format without content).
    * @param table
    * @return a shallow copy of the table
    */
    public static function shallowCopy(PdfPTable $table) {
        $nt = new PdfPTable();
        $nt->copyFormat($table);
        return $nt;
    }

    /**
    * Copies the format of the sourceTable without copying the content. 
    * @param sourceTable
    */
    private function copyFormat(PdfPTable $sourceTable) {
        $relativeWidths = array();
        $absoluteWidths = array();
        $relativeWidths = array_merge(array(), $sourceTable->relativeWidths);
        $absoluteWidths = array_merge(array(), $sourceTable->absoluteWidths);        $totalWidth = $sourceTable->totalWidth;
        $totalHeight = $sourceTable->totalHeight;
        $currentRowIdx = 0;
        $tableEvent = $sourceTable->tableEvent;
        $runDirection = $sourceTable->runDirection;
        $defaultCell = new PdfPCell($sourceTable->defaultCell);
        $currentRow = array();
        $isColspan = $sourceTable->isColspan;
        $splitRows = $sourceTable->splitRows;
        $spacingAfter = $sourceTable->spacingAfter;
        $spacingBefore = $sourceTable->spacingBefore;
        $headerRows = $sourceTable->headerRows;
        $lockedWidth = $sourceTable->lockedWidth;
        $extendLastRow = $sourceTable->extendLastRow;
        $headersInEvent = $sourceTable->headersInEvent;
        $widthPercentage = $sourceTable->widthPercentage;
        $splitLate = $sourceTable->splitLate;
        $skipFirstHeader = $sourceTable->skipFirstHeader;
        $horizontalAlignment = $sourceTable->horizontalAlignment;
    }

    public function setWidths(array $relativeWidths)
    {
        if (is_integer($relativeWidths[0]) == TRUE)
        {
            setWidthsIntegerArray($relativeWidths);
        }
        else
        {
            setWidthsFloatArray($relativeWidths);
        }
    }



    /** Sets the relative widths of the table.
    * @param relativeWidths the relative widths of the table.
    * @throws DocumentException if the number of widths is different than the number
    * of columns
    */
    private function setWidthsIntegerArray(array $relativeWidths)
    {
        $tb = array();
        for ($k = 0; $k < count($relativeWidths); ++$k)
            $tb[$k] = $relativeWidths[$k];
        setWidthsFloatArray($tb);
    }


    /** Sets the relative widths of the table.
    * @param relativeWidths the relative widths of the table.
    * @throws DocumentException if the number of widths is different than the number
    * of columns
    */
    private function setWidthsFloatArray(array $relativeWidths)
    {
        if (count($relativeWidths) != count($this->relativeWidths))
            throw new DocumentException("Wrong number of columns.");
        $this->relativeWidths = array();
        $this->relativeWidths = array_merge(array(), $relativeWidths);
        $absoluteWidths = array();
        totalHeight = 0;
        calculateWidths();
        calculateHeights();
    }

    private function calculateWidths() {
        if ($totalWidth <= 0)
            return;
        $total = 0.0;
        for ($k = 0; k < count($absoluteWidths); ++$k) {
            $total += $relativeWidths[$k];
        }
        for ($k = 0; $k < count($absoluteWidths); ++$k) {
            $absoluteWidths[$k] = $totalWidth * $relativeWidths[$k] / $total;
        }
    }

    public function setTotalWidth()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_float($arg1) == TRUE)
                {
                    setTotalWidthFloat($arg1);
                }
                else if (is_array($arg1) == TRUE)
                {
                    setTotalWidthArray($arg1);
                }
                break;
            }
        }
    }


    /** Sets the full width of the table.
    * @param totalWidth the full width of the table.
    */
    private function setTotalWidthFloat($totalWidth) {
        if ($this->totalWidth == $totalWidth)
            return;
        $this->totalWidth = $totalWidth;
        $totalHeight = 0.0;
        calculateWidths();
        calculateHeights();
    }

    /** Sets the full width of the table from the absolute column width.
    * @param columnWidth the absolute width of each column
    * @throws DocumentException if the number of widths is different than the number
    * of columns
    */
    private function setTotalWidthArray(array $columnWidth) {
        if (count($columnWidth) != count($this->relativeWidths))
            throw new DocumentException("Wrong number of columns.");
        $totalWidth = 0.0;
        for ($k = 0; $k < count($columnWidth); ++%k)
            $totalWidth += $columnWidth[$k];
        setWidths($columnWidth);
    }


    /** Sets the percentage width of the table from the absolute column width.
    * @param columnWidth the absolute width of each column
    * @param pageSize the page size
    * @throws DocumentException
    */
    public function setWidthPercentage(array $columnWidth, Rectangle $pageSize) {
        if (count($columnWidth) != count($this->relativeWidths))
            throw new IllegalArgumentException("Wrong number of columns.");
        $totalWidth = 0.0;
        for ($k = 0; $k < count($columnWidth); ++$k)
            $totalWidth += $columnWidth[$k];
        $widthPercentage = $totalWidth / ($pageSize->right() - $pageSize->left()) * 100.0;
        setWidths($columnWidth);
    }

    /** Gets the full width of the table.
    * @return the full width of the table
    */
    public function getTotalWidth() {
        return $totalWidth;
    }

    protected function calculateHeights() {
        if ($totalWidth <= 0.0)
            return;
        $totalHeight = 0.0;
        for ($k = 0; $k < count($rows); ++$k) {
            $row = $rows[$k];
            if ($row != NULL) {
                $row->setWidths($absoluteWidths);
                $totalHeight += $row->getMaxHeights();
            }
        }
    }

    /**
    * Calculates the heights of the table.
    */
    public function calculateHeightsFast() {
        if ($totalWidth <= 0.0)
            return;
        $totalHeight = 0.0;
        for ($k = 0; $k < count($rows); ++$k) {
            $row = $rows[$k];
            if ($row != NULL)
                $totalHeight += $row->getMaxHeights();
        }
    }

    /** Gets the default <CODE>PdfPCell</CODE> that will be used as
    * reference for all the <CODE>addCell</CODE> methods except
    * <CODE>addCell(PdfPCell)</CODE>.
    * @return default <CODE>PdfPCell</CODE>
    */
    public function getDefaultCell() {
        return $defaultCell;
    }


    public function addCell()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof PdfPCell)
                {
                    addCellPdfPCell($arg1);
                }
                else if (is_string($arg1) == TRUE)
                {
                    addCellString($arg1);
                }
                else if ($arg1 instanceof PdfPTable)
                {
                    addCellPdfPTable($arg1);
                }
                else if ($arg1 instanceof Image)
                {
                    addCellImage($arg1);
                }
                else if ($arg1 instanceof Phrase)
                {
                    addCellPhrase($arg1);
                }
                break;
            }
        }
    }


    /** Adds a cell element.
    * @param cell the cell element
    */
    private function addCellPdfPCell(PdfPCell $cell) {
        $ncell = new PdfPCell($cell);
        $colspan = $ncell->getColspan();
        $colspan = max($colspan, 1);
        $colspan = min($colspan, count($currentRow) - $currentRowIdx);
        $ncell->setColspan($colspan);
        if ($colspan != 1)
            $isColspan = TRUE;
        $rdir = $ncell->getRunDirection();
        if ($rdir == PdfWriter::RUN_DIRECTION_DEFAULT)
            $ncell->setRunDirection($runDirection);
        $currentRow[$currentRowIdx] = $ncell;
        $currentRowIdx += $colspan;
        if ($currentRowIdx >= count($currentRow)) {
            if ($runDirection == PdfWriter::RUN_DIRECTION_RTL) {
                $rtlRow = array();
                $rev = count($currentRow);
                for ($k = 0; $k < count($currentRow); ++$k) {
                    $rcell = $currentRow[$k];
                    $cspan = $rcell->getColspan();
                    $rev -= $cspan;
                    $rtlRow[$rev] = $rcell;
                    $k += $cspan - 1;
                }
                $currentRow = $rtlRow;
            }
            $row = new PdfPRow($currentRow);
            if ($totalWidth > 0.0) {
                $row->setWidths($absoluteWidths);
                $totalHeight += $row->getMaxHeights();
            }
            array_push($rows, $row);
            $currentRow = array();
            $currentRowIdx = 0;
        }
    }

    /** Adds a cell element.
    * @param text the text for the cell
    */
    private function addCellString($text) {
        addCell(new Phrase($text));
    }

    /**
    * Adds a nested table.
    * @param table the table to be added to the cell
    */
    private function addCellPdfPTable(PdfPTable $table) {
        $defaultCell->setTable($table);
        addCell($defaultCell);
        $defaultCell->setTable(NULL);
    }

    /**
    * Adds an Image as Cell.
    * @param image the <CODE>Image</CODE> to add to the table. This image will fit in the cell
    */
    private function addCellImage(Image $image) {
        $defaultCell->setImage($image);
        addCell($defaultCell);
        $defaultCell->setImage(NULL);
    }

    /**
    * Adds a cell element.
    * @param phrase the <CODE>Phrase</CODE> to be added to the cell
    */
    private function addCell(Phrase $phrase) {
        $defaultCell->setPhrase($phrase);
        addCell($defaultCell);
        $defaultCell->setPhrase(NULL);
    }


    public function writeSelectedRows()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 5:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_array($arg5) == TRUE)
                {
                    return writeSelectedRowsArray($arg1, $arg2, $arg3, $arg4, $arg5);
                }
                else if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && $arg5 instanceof PdfContentByte)
                {
                    return writeSelectedRowsPdfContentByte($arg1, $arg2, $arg3, $arg4, $arg5);
                }
                break;
            }
            case 7:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE && is_integer($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE && is_array($arg7) == TRUE)
                {
                    return writeSelectedRows7ArgsArray($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
                }
                else if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE && is_integer($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE && $arg7 instanceof PdfContentByte)
                {
                    return writeSelectedRows7ArgsPdfContentByte($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
                }
                break;
            }
        }
    }


    /**
    * Writes the selected rows to the document.
    * <P>
    * <CODE>canvases</CODE> is obtained from <CODE>beginWritingRows()</CODE>.
    * @param rowStart the first row to be written, zero index
    * @param rowEnd the last row to be written + 1. If it is -1 all the
    * rows to the end are written
    * @param xPos the x write coodinate
    * @param yPos the y write coodinate
    * @param canvases an array of 4 <CODE>PdfContentByte</CODE> obtained from
    * <CODE>beginWrittingRows()</CODE>
    * @return the y coordinate position of the bottom of the last row
    * @see #beginWritingRows(com.lowagie.text.pdf.PdfContentByte)
    */
    private function writeSelectedRowsArray($rowStart, $rowEnd, $xPos, $yPos, array $canvases) {
        return writeSelectedRows(0, -1, $rowStart, $rowEnd, $xPos, $yPos, $canvases);
    }


    /** Writes the selected rows and columns to the document.
    * This method does not clip the columns; this is only important
    * if there are columns with colspan at boundaries.
    * <P>
    * <CODE>canvases</CODE> is obtained from <CODE>beginWritingRows()</CODE>.
    * <P>
    * The table event is only fired for complete rows.
    * @param colStart the first column to be written, zero index
    * @param colEnd the last column to be written + 1. If it is -1 all the
    * columns to the end are written
    * @param rowStart the first row to be written, zero index
    * @param rowEnd the last row to be written + 1. If it is -1 all the
    * rows to the end are written
    * @param xPos the x write coodinate
    * @param yPos the y write coodinate
    * @param canvases an array of 4 <CODE>PdfContentByte</CODE> obtained from
    * <CODE>beginWrittingRows()</CODE>
    * @return the y coordinate position of the bottom of the last row
    * @see #beginWritingRows(com.lowagie.text.pdf.PdfContentByte)
    */
    private function writeSelectedRows7ArgsArray($colStart, $colEnd, $rowStart, $rowEnd, $xPos, $yPos, array $canvases) {
        if ($totalWidth <= 0.0)
            throw new Exception("The table width must be greater than zero.");
        $size = count($rows);
        if ($rowEnd < 0)
            $rowEnd = $size;
        $rowEnd = min($rowEnd, $size);
        if ($rowStart < 0)
            $rowStart = 0;
        if ($rowStart >= $rowEnd)
            return $yPos;
        if ($colEnd < 0)
            $colEnd = count($absoluteWidths);
        $colEnd = min($colEnd, count($absoluteWidths));
        if ($colStart < 0)
            $colStart = 0;
        $colStart = min($colStart, count($absoluteWidths));
        $yPosStart = $yPos;
        for ($k = $rowStart; $k < $rowEnd; ++$k) {
            $row = $rows[$k];
            if ($row != NULL) {
                $row->writeCells($colStart, $colEnd, $xPos, $yPos, $canvases);
                $yPos -= $row->getMaxHeights();
            }
        }
        if ($tableEvent != NULL && $colStart == 0 && $colEnd == count($absoluteWidths)) {
            $heights = array();
            $heights[0] = $yPosStart;
            for ($k = $rowStart; $k < $rowEnd; ++$k) {
                $row = $rows[$k];
                $hr = 0.0;
                if ($row != NULL)
                    $hr = $row->getMaxHeights();
                $heights[$k - $rowStart + 1] = $heights[$k - $rowStart] - $hr;
            }
            $tableEvent->tableLayout($this, getEventWidths($xPos, $rowStart, $rowEnd, $headersInEvent), $heights, $headersInEvent ? $headerRows : 0, $rowStart, $canvases);
        }
        return $yPos;
    }


    /**
    * Writes the selected rows to the document.
    * 
    * @param rowStart the first row to be written, zero index
    * @param rowEnd the last row to be written + 1. If it is -1 all the
    * rows to the end are written
    * @param xPos the x write coodinate
    * @param yPos the y write coodinate
    * @param canvas the <CODE>PdfContentByte</CODE> where the rows will
    * be written to
    * @return the y coordinate position of the bottom of the last row
    */
    private function writeSelectedRowsPdfContentByte($rowStart, $rowEnd, $xPos, $yPos, PdfContentByte $canvas) {
        return writeSelectedRows(0, -1, $rowStart, $rowEnd, $xPos, $yPos, $canvas);
    }


    /**
    * Writes the selected rows to the document.
    * This method clips the columns; this is only important
    * if there are columns with colspan at boundaries.
    * <P>
    * The table event is only fired for complete rows.
    *
    * @param colStart the first column to be written, zero index
    * @param colEnd the last column to be written + 1. If it is -1 all the
    * @param rowStart the first row to be written, zero index
    * @param rowEnd the last row to be written + 1. If it is -1 all the
    * rows to the end are written
    * @param xPos the x write coodinate
    * @param yPos the y write coodinate
    * @param canvas the <CODE>PdfContentByte</CODE> where the rows will
    * be written to
    * @return the y coordinate position of the bottom of the last row
    */
    private function writeSelectedRows7ArgsPdfContentByte($colStart, $colEnd, $rowStart, $rowEnd, $xPos, $yPos, PdfContentByte $canvas) {
        if ($colEnd < 0)
            $colEnd = count($absoluteWidths);
        $colEnd = min($colEnd, count($absoluteWidths));
        if ($colStart < 0)
            $colStart = 0;
        $colStart = min($colStart, count($absoluteWidths));
        if ($colStart != 0 || $colEnd != count($absoluteWidths)) {
            $w = 0.0;
            for ($k = $colStart; $k < $colEnd; ++$k)
                $w += $absoluteWidths[$k];
            $canvas->saveState();
            $lx = 0.0;
            $rx = 0.0;
            if ($colStart == 0)
                $lx = 10000;
            if ($colEnd == count($absoluteWidths))
                $rx = 10000;
            $canvas->rectangle($xPos - $lx, -10000, $w + $lx + $rx, 20000);
            $canvas->clip();
            $canvas->newPath();
        }
        $canvases = PdfPTable::beginWritingRows($canvas);
        $y = writeSelectedRows($colStart, $colEnd, $rowStart, $rowEnd, $xPos, $yPos, $canvases);
        PdfPTable::endWritingRows($canvases);
        if ($colStart != 0 || $colEnd != count($absoluteWidths))
            $canvas->restoreState();
        return $y;
    }

    /** Gets and initializes the 4 layers where the table is written to. The text or graphics are added to
    * one of the 4 <CODE>PdfContentByte</CODE> returned with the following order:<p>
    * <ul>
    * <li><CODE>PdfPtable.BASECANVAS</CODE> - the original <CODE>PdfContentByte</CODE>. Anything placed here
    * will be under the table.
    * <li><CODE>PdfPtable.BACKGROUNDCANVAS</CODE> - the layer where the background goes to.
    * <li><CODE>PdfPtable.LINECANVAS</CODE> - the layer where the lines go to.
    * <li><CODE>PdfPtable.TEXTCANVAS</CODE> - the layer where the text go to. Anything placed here
    * will be over the table.
    * </ul><p>
    * The layers are placed in sequence on top of each other.
    * @param canvas the <CODE>PdfContentByte</CODE> where the rows will
    * be written to
    * @return an array of 4 <CODE>PdfContentByte</CODE>
    * @see #writeSelectedRows(int, int, float, float, PdfContentByte[])
    */
    public static function beginWritingRows(PdfContentByte $canvas) {
        $tmpArray = array();
        array_push($tmpArray, $canvas);
        array_push($tmpArray, $canvas->getDuplicate());
        array_push($tmpArray, $canvas->getDuplicate());
        array_push($tmpArray, $canvas->getDuplicate());
        return $tmpArray;
    }

    /** Finishes writing the table.
    * @param canvases the array returned by <CODE>beginWritingRows()</CODE>
    */
    public static function endWritingRows(array $canvases) {
        $canvas = $canvases[PdfPTable::BASECANVAS];
        $canvas->saveState();
        $canvas->add($canvases[PdfPTable::BACKGROUNDCANVAS]);
        $canvas->restoreState();
        $canvas->saveState();
        $canvas->setLineCap(2);
        $canvas->resetRGBColorStroke();
        $canvas->add($canvases[PdfPTable::LINECANVAS]);
        $canvas->restoreState();
        $canvas->add($canvases[PdfPTable::TEXTCANVAS]);
    }

    /** Gets the number of rows in this table.
    * @return the number of rows in this table
    */    
    public function size() {
        return count($rows);
    }

    /** Gets the total height of the table.
    * @return the total height of the table
    */
    public function getTotalHeight() {
        return $totalHeight;
    }

    /** Gets the height of a particular row.
    * @param idx the row index (starts at 0)
    * @return the height of a particular row
    */
    public function getRowHeight($idx) {
        if ($totalWidth <= 0.0 || $idx < 0 || $idx >= count($rows))
            return 0;
        $row = $rows[$idx];
        if ($row == NULL)
            return 0;
        return $row->getMaxHeights();
    }

    /** Gets the height of the rows that constitute the header as defined by
    * <CODE>setHeaderRows()</CODE>.
    * @return the height of the rows that constitute the header
    */
    public function getHeaderHeight() {
        $total = 0.0;
        $size = min(count($rows), $headerRows);
        for ($k = 0; $k < $size; ++$k) {
            $row = $rows[$k];
            if ($row != NULL)
                $total += $row->getMaxHeights();
        }
        return $total;
    }

    /** Deletes a row from the table.
    * @param rowNumber the row to be deleted
    * @return <CODE>true</CODE> if the row was deleted
    */
    public function deleteRow($rowNumber) {
        if ($rowNumber < 0 || $rowNumber >= count($rows)) {
            return FALSE;
        }
        if ($totalWidth > 0.0) {
            $row = $rows[$rowNumber];
            if ($row != NULL)
                $totalHeight -= $row->getMaxHeights();
        }
        unset($rows[$rowNumber]);
        return TRUE;
    }

    /** Deletes the last row in the table.
    * @return <CODE>true</CODE> if the last row was deleted
    */
    public function deleteLastRow() {
        return deleteRow(count($rows) - 1);
    }

    /**
    * Removes all of the rows except headers
    */
    public function deleteBodyRows() {
        $rows2 = array();
        for ($k = 0; $k < $headerRows; ++$k)
            array_push($rows2, $rows[$k]);
        $rows = $rows2;
        $totalHeight = 0.0;
        if ($totalWidth > 0.0)
            $totalHeight = getHeaderHeight();
    }

    /** Gets the number of the rows that constitute the header.
    * @return the number of the rows that constitute the header
    */
    public function getHeaderRows() {
        return $headerRows;
    }


    /** Sets the number of the top rows that constitute the header.
    * This header has only meaning if the table is added to <CODE>Document</CODE>
    * and the table crosses pages.
    * @param headerRows the number of the top rows that constitute the header
    */
    public function setHeaderRows($headerRows) {
        if ($headerRows < 0)
            $headerRows = 0;
        $this->headerRows = $headerRows;
    }

    /**
    * Gets all the chunks in this element.
    *
    * @return	an <CODE>ArrayList</CODE>
    */
    public function getChunks() {
        return array();
    }

    /**
    * Gets the type of the text element.
    *
    * @return	a type
    */
    public function type() {
        return Element::PTABLE;
    }

    /**
    * Processes the element by adding it (or the different parts) to an
    * <CODE>ElementListener</CODE>.
    *
    * @param	listener	an <CODE>ElementListener</CODE>
    * @return	<CODE>true</CODE> if the element was processed successfully
    */
    public function process(ElementListener $listener) {
        try {
            return $listener->add($this);
        }
        catch(DocumentException $de) {
            return FALSE;
        }
    }


    /** Gets the width percentage that the table will occupy in the page.
    * @return the width percentage that the table will occupy in the page
    */
    public function getWidthPercentage() {
        return $widthPercentage;
    }

    /** Sets the width percentage that the table will occupy in the page.
    * @param widthPercentage the width percentage that the table will occupy in the page
    */
    public function setWidthPercentage($widthPercentage) {
        $this->widthPercentage = $widthPercentage;
    }

    /** Gets the horizontal alignment of the table relative to the page.
    * @return the horizontal alignment of the table relative to the page
    */
    public function getHorizontalAlignment() {
        return $horizontalAlignment;
    }

    /** Sets the horizontal alignment of the table relative to the page.
    * It only has meaning if the width percentage is less than
    * 100%.
    * @param horizontalAlignment the horizontal alignment of the table relative to the page
    */
    public function setHorizontalAlignment($horizontalAlignment) {
        $this->horizontalAlignment = $horizontalAlignment;
    }


    /**
    * Gets a row with a given index
    * (added by Jin-Hsia Yang).
    * @param idx
    * @return the row at position idx
    */
    public function getRow($idx) {
        return $rows[$idx];
    }

    /**
    * Gets an arraylist with all the rows in the table.
    * @return an arraylist
    */
    public function getRows() {
        return $rows;
    }

    /** Sets the table event for this table.
    * @param event the table event for this table
    */
    public function setTableEvent(PdfPTableEvent $event) {
        $tableEvent = $event;
    }

    /** Gets the table event for this page.
    * @return the table event for this page
    */
    public function getTableEvent() {
        return $tableEvent;
    }

    /** Gets the absolute sizes of each column width.
    * @return he absolute sizes of each column width
    */
    public function getAbsoluteWidths() {
        return $absoluteWidths;
    }

    function getEventWidths($xPos, $firstRow, $lastRow, $includeHeaders) {
        if ($includeHeaders == TRUE) {
            $firstRow = max($firstRow, $headerRows);
            $lastRow = max($lastRow, $headerRows);
        }
        $widths = array();
        if ($isColspan == TRUE) {
            $n = 0;
            if ($includeHeaders == TRUE) {
                for ($k = 0; $k < $headerRows; ++$k) {
                    $row = $rows[$k];
                    if ($row == NULL)
                        ++$n;
                    else
                        $widths[$n++] = $row->getEventWidth($xPos);
                }
            }
            for (; $firstRow < $lastRow; ++$firstRow) {
                    $row = $rows[$firstRow];
                    if ($row == NULL)
                        ++$n;
                    else
                        $widths[$n++] = $row->getEventWidth($xPos);
            }
        }
        else {
            $width = array();
            $width[0] = $xPos;
            for ($k = 0; $k < count($absoluteWidths); ++$k)
                $width[$k + 1] = $width[$k] + $absoluteWidths[$k];
            for ($k = 0; $k < (($includeHeaders ? $headerRows : 0) + $lastRow - $firstRow); ++$k)
                $widths[k] = $width;
        }
        return $widths;
    }


    /** Getter for property skipFirstHeader.
    * @return Value of property skipFirstHeader.
    */
    public function isSkipFirstHeader() {
        return $skipFirstHeader;
    }

    /** Skips the printing of the first header. Used when printing
    * tables in succession belonging to the same printed table aspect.
    * @param skipFirstHeader New value of property skipFirstHeader.
    */
    public function setSkipFirstHeader($skipFirstHeader) {
        $this->skipFirstHeader = $skipFirstHeader;
    }

    /**
    * Sets the run direction of the contents of the table.
    * @param runDirection
    */
    public function setRunDirection($runDirection) {
        if ($runDirection < PdfWriter::RUN_DIRECTION_DEFAULT || $runDirection > PdfWriter::RUN_DIRECTION_RTL)
            throw new Exception("Invalid run direction: " + runDirection);
        this.runDirection = runDirection;
    }

    /**
    * Returns the run direction of the contents in the table.
    * @return One of the following values: PdfWriter.RUN_DIRECTION_DEFAULT, PdfWriter.RUN_DIRECTION_NO_BIDI, PdfWriter.RUN_DIRECTION_LTR or PdfWriter.RUN_DIRECTION_RTL.
    */
    public function getRunDirection() {
        return $runDirection;
    }

    /**
    * Getter for property lockedWidth.
    * @return Value of property lockedWidth.
    */
    public function isLockedWidth() {
        return $this->lockedWidth;
    }

    /**
    * Uses the value in <CODE>setTotalWidth()</CODE> in <CODE>Document.add()</CODE>.
    * @param lockedWidth <CODE>true</CODE> to use the value in <CODE>setTotalWidth()</CODE> in <CODE>Document.add()</CODE>
    */
    public function setLockedWidth($lockedWidth) {
        $this->lockedWidth = $lockedWidth;
    }

    /**
    * Gets the split value.
    * @return true to split; false otherwise
    */
    public function isSplitRows() {
        return $this->splitRows;
    }

    /**
    * When set the rows that won't fit in the page will be split. 
    * Note that it takes at least twice the memory to handle a split table row
    * than a normal table. <CODE>true</CODE> by default.
    * @param splitRows true to split; false otherwise
    */
    public function setSplitRows($splitRows) {
        $this->splitRows = $splitRows;
    }

    /**
    * Sets the spacing before this table.
    *
    * @param	spacing		the new spacing
    */

    public function setSpacingBefore($spacing) {
        $this->spacingBefore = $spacing;
    }

    /**
    * Sets the spacing after this table.
    *
    * @param	spacing		the new spacing
    */

    public function setSpacingAfter($spacing) {
        $this->spacingAfter = $spacing;
    }

    /**
    * Gets the spacing before this table.
    *
    * @return	the spacing
    */

    public function spacingBefore() {
        return $spacingBefore;
    }

    /**
    * Gets the spacing before this table.
    *
    * @return	the spacing
    */

    public function spacingAfter() {
        return $spacingAfter;
    }

    /**
    *  Gets the value of the last row extension.
    * @return true if the last row will extend; false otherwise
    */
    public function isExtendLastRow() {
        return $this->extendLastRow;
    }

    /**
    * When set the last row will be extended to fill all the remaining space to the
    * bottom boundary.
    * @param extendLastRow true to extend the last row; false otherwise
    */
    public function setExtendLastRow($extendLastRow) {
        $this->extendLastRow = $extendLastRow;
    }

    /**
    * Gets the header status inclusion in PdfPTableEvent.
    * @return true if the headers are included; false otherwise
    */
    public function isHeadersInEvent() {
        return $this->headersInEvent;
    }

    /**
    * When set the PdfPTableEvent will include the headers.
    * @param headersInEvent true to include the headers; false otherwise
    */
    public function setHeadersInEvent($headersInEvent) {
        $this->headersInEvent = $headersInEvent;
    }

    /**
    * Gets the property splitLate.
    * @return the property splitLate
    */
    public function isSplitLate() {
        return $this->splitLate;
    }

    /**
    * If true the row will only split if it's the first one in an empty page.
    * It's true by default.
    *<p>
    * It's only meaningful if setSplitRows(true).
    * @param splitLate the property value
    */
    public function setSplitLate($splitLate) {
        $this->splitLate = $splitLate;
    }






}





?>