# PR PDF Formatting Fix Plan

Update the PR PDF export (generated from the Excel template via PhpSpreadsheet + mPDF) by adjusting only the existing `PrReviewController::exportPdf()` spreadsheet styling/mapping to force a single-page output, correct fonts/sizes, eliminate header gap, prevent unwanted line wraps, apply thick outside borders on required blocks, and bind the Total to `pr_tbl.pr_total`.

## Context / Where the PDF is generated
- **PDF is not rendered from `resources/views/head/pages/head-pr-review.blade.php`**.
- The **PDF is generated in** `app/Http/Controllers/PrReviewController.php` method `exportPdf($task_id)`.
- It loads an Excel template: `procurement_documents/Purchase Request Excel Template.xlsx`, fills values, applies styles, then exports via `PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf`.

## Goals and minimal-change strategy
- **Keep using the existing Excel template** and only adjust:
  - Page setup / print area / fit-to-page.
  - Row heights & alignment in rows 1–7 (institutional header).
  - Font sizes and wrapping flags for specific cells.
  - Border “outline” thickness for the specified blocks.
  - Total rendering behavior to match your desired source of truth.

## Template structure reference (from `procurement_documents/purchase_request _field_mapping.json`)
- **Print area**: `A1:G51`
- **Institutional header**: rows **1–6** (readonly)
- **Items table**: header row **11**, item rows **12–45**
- **Total row**: row **46**
  - `A46:E46` = TOTAL label (merged, readonly)
  - `G46` = grand total (template expects SUM formula, readonly)
- **Footer**: rows **47–51**
  - `A50:B50` = `NAME OF REQUESTOR` label (readonly)
  - `C50:D50` = requestor name input
  - `E50:G50` = Campus Director name (static: `Engr. REXMELLE F. DECAPIA, JR. Ph.D.`) **DO NOT overwrite**
  - `E51:G51` = Campus Director title (static) **DO NOT overwrite**

## Implementation steps (recommended order)

### 1) Enforce “PDF should only be one page”
- **Keep**:
  - `setFitToPage(true)`
  - `setFitToWidth(1)`
  - `setFitToHeight(1)`
  - `setOrientation(PORTRAIT)`
  - `setPaperSize(A4)`
- **Verify / adjust print area** so it exactly covers what you want printed:
  - Currently: `setPrintArea('A1:G51')`.
  - Ensure no extra rows/columns beyond the footer are included (otherwise you may get a page 2).
- If content still spills:
  - Slightly reduce margins (e.g. from `0.5` to `0.3`) OR reduce row heights in the header/footer first.

### 2) Use Arial Narrow font
- Already present and should remain:
  - `$spreadsheet->getDefaultStyle()->getFont()->setName('Arial Narrow');`
- **No additional font embedding work** unless your PDF renderer machine lacks Arial Narrow.

### 3) Remove whitespace gap under “TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES”
- You already set: `$sheet->getRowDimension(7)->setRowHeight(0);`
- Also tighten header row heights (rows 1–6):
  - Replace the current heuristic row height:
    - `setRowHeight(isset($headerStyles["B{$i}"]) ? $headerStyles["B{$i}"] + 8 : 15)`
  - With explicit row heights per row (beginner-friendly, predictable):
    - Example: row 1 small, row 2–5 moderate, row 6 minimal.
- Ensure the header cells have:
  - `->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)` (or TOP) so text doesn’t introduce extra vertical space.

### 4) Keep specific texts on one line (no breaks)
Requirement: “PURCHASE REQUEST”, “NAME OF REQUESTOR”, and `Engr. REXMELLE F. DECAPIA, JR. Ph.D.` should not break.
- In `exportPdf()`, apply `wrapText(false)` and optionally `shrinkToFit(true)` to the **exact cells** where those strings live in the template.
  - “PURCHASE REQUEST” currently styled at `E3`.
  - `NAME OF REQUESTOR` label is at `A50` (readonly, but we can still enforce no-wrap for safety/consistency).
  - Requestor name is written to `C50` (`C50:D50` merged).
  - Campus Director name is in `E50:G50` and is **hardcoded in the template** (do not overwrite; only ensure it stays single-line).
- Add alignment + shrink settings for those cells:
  - `->setWrapText(false)`
  - `->setShrinkToFit(true)` (useful if the cell width is insufficient)
  - Optionally reduce font size *slightly* only for the director line if shrink-to-fit still overflows.

### 5) Reduce height (vertical) of `institutional_header` (rows 1–6)
- Set row heights explicitly (minimal trial-and-error):
  - Rows 1–6: reduce to the smallest values that still look correct.
- Avoid using “fontSize + 8” row height because it can be too tall for Arial Narrow.

### 6) Thick outside borders for header / items table / total row / footer
- This is already implemented using an outline thick border style:
  - Header: `A1:G6`
  - Items table: `A11:G45`
  - Total row: `A46:G46`
  - Footer: `A47:G51`
- Verify the ranges match your template:
  - Institutional header is **rows 1–6** (matches your requirement `row_end: 6`).
  - Items table start row should match the template’s table border (you currently use `A11:G45`).
- If you visually see missing thick edges, adjust the target ranges to match the real block boundaries in the template.

### 7) Fix “Total” to use database `pr_tbl.pr_total`
There is an important constraint from the field-mapping guide:
- The template defines `G46` as **readonly** and typically a **SUM formula** (`=SUM(G12:G45)`), and the guide explicitly says “Do not write to this cell.”

Decision confirmed: **Option A (template-compliant)**.

Implementation rules for Option A:
- **Do not write** `$pr->pr_total` into `G46`.
- Ensure the sheet produces a **numeric total** via formulas:
  - For each populated item row, `G{row}` must be a formula: `=A{row}*E{row}` (inject if missing).
  - `G46` must be `=SUM(G12:G45)` (inject if missing).
- Apply number formatting so the PDF renders as a number (not text):
  - `G12:G46` format `#,##0.00`.
- Ensure formulas are actually calculated before writing PDF (common cause of totals showing as blank/incorrect in PDF writers):
  - Use PhpSpreadsheet’s calculation engine to calculate or pre-calculate the workbook before passing it to the PDF writer.

Expected outcome:
- The PDF “TOTAL” shows the calculated sum (numeric), and the DB `pr_total` should match because it is derived from the same items.

## Font size checklist (as requested)
Set/verify the following sizes in the header/footer blocks (matching your current code style):
- `B2` Republic of the Philippines = **11**
- `B3` TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES = **12**
- `B4` Address line = **11**
- `B5` Website/telephone line = **10.5**
- `E2` PROCUREMENT OFFICE = **11**
- `E3` PURCHASE REQUEST = **20**
- `E5` FM-PR-007 REV info = **9**
- Items table section (`A12:G45`) = **12**
- TOTAL row (`A46:G46`) = **14**
- Footer section (`A47:G51`) = **11**

## Clarifying questions (to avoid wrong cell targeting)
- In your Excel template, what exact cells contain:
  - The label “NAME OF REQUESTOR”?
  - The director name `Engr. REXMELLE F. DECAPIA, JR. Ph.D.`?
- Is the director line supposed to be **exactly that constant text**, or should it come from `$pr->approver`?

## Resolved from your reply
- `NAME OF REQUESTOR` label is at `A50`.
- Campus Director name is **hardcoded** (constant) in the template at `E50:G50`.

## Acceptance checks (quick)
- Exported PDF downloads as a **single page**.
- All specified header lines have the correct font sizes and **Arial Narrow**.
- No visible gap under the university name.
- The three specified strings stay on **one line**.
- Thick outline borders appear around:
  - Rows 1–6 (header)
  - Items table block
  - Total row
  - Footer block
- Total equals the database value `pr_tbl.pr_total`.
