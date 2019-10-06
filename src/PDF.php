<?php
	namespace Knister;
	use FPDF;

	class PDF extends FPDF {
		function Table($header, $columns, $bBorder, $colWidth, $colHeight) {
			$iBorder = ($bBorder == true) ? 1 : 0;
			//Header
			foreach ($header as $col) {
				$this->Cell($colWidth, $colHeight, $col, $iBorder);
			}
			$this->Ln();
			//Data
			foreach ($columns as $row) {
				foreach ($row as $col) {
					$this->Cell($colWidth, $colHeight, $col, $iBorder);
				}
				$this->Ln();
			}
		}
	}