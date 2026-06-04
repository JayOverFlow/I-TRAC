# Generating Delivery Attachments based on Items Category

This plan covers the implementation for generating, validating, storing, and viewing Delivery Attachments (IAR, RIS, RSMI, ICS, RSPI, PAR, and NDR) based on user categorization and quantity distribution of PO items.

## User Review Required

> [!IMPORTANT]
> **Database Schema Alteration Required**
> - The current schema `po_items_category` enum column lacks the `'Not Delivered'` option, which must be added.
> - There are currently no tables for Non-Delivery Report (`ndr_tbl`, `ndr_items_tbl`, and `ndr_items_specs_tbl`).
> - We propose running raw SQL queries (shown below) to modify the database.

## Open Questions

> [!WARNING]
> 1. **Serial Number Formatting**: We propose auto-generating document numbers with standard formats like `IAR-YYYY-MM-XXXX`, `RIS-YYYY-MM-XXXX`, `ICS-YYYY-MM-XXXX`, `PAR-YYYY-MM-XXXX`, `RSMI-YYYY-MM-XXXX`, `RSPI-YYYY-MM-XXXX`, and `NDR-YYYY-MM-XXXX` where `XXXX` is sequential/unique (or timestamp-based). If there is a different standard template required, please let us know.
> 2. **Department Entity**: For `ris_tbl`, the `ris_office` column stores the office/department name. In the front-end department modal, the department is a free-form string input. We will insert the text directly into the DB.
> 3. **Non-Delivery Report Table**: The database does not contain `ndr_tbl`, `ndr_items_tbl`, or `ndr_items_specs_tbl`. We assumed a table structure matching other slips. Please verify if this matches your requirements.

---

## Proposed Changes

### Database Modfications

#### [NEW] [01.06.2026_delivery_attachments.sql](file:///c:/Users/emman/itrac/database_schema/01.06.2026_delivery_attachments.sql)
Create raw SQL script for manual execution in Workbench:
```sql
-- 1. Modify po_items_tbl enum
ALTER TABLE `po_items_tbl` MODIFY COLUMN `po_items_category` enum('Supply and Materials','Semi-Expendable','Equipment','Not Delivered') DEFAULT NULL;

-- 2. Create ndr_tbl
CREATE TABLE IF NOT EXISTS `ndr_tbl` (
  `ndr_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_id_fk` bigint unsigned DEFAULT NULL,
  `ndr_no` varchar(50) DEFAULT NULL,
  `ndr_date` date DEFAULT NULL,
  `ndr_reported_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ndr_id`),
  KEY `idx_ndr_po_fk` (`po_id_fk`),
  KEY `idx_ndr_reported_by` (`ndr_reported_by`),
  CONSTRAINT `fk_ndr_po_ref` FOREIGN KEY (`po_id_fk`) REFERENCES `po_tbl` (`po_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ndr_reported_by` FOREIGN KEY (`ndr_reported_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Create ndr_items_tbl
CREATE TABLE IF NOT EXISTS `ndr_items_tbl` (
  `ndr_items_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ndr_id_fk` bigint unsigned NOT NULL,
  `ndr_po_items_id_fk` bigint unsigned DEFAULT NULL,
  `ndr_stock_no` varchar(50) DEFAULT NULL,
  `ndr_unit` varchar(20) DEFAULT NULL,
  `ndr_items_descrip` varchar(255) DEFAULT NULL,
  `ndr_quantity` int DEFAULT NULL,
  PRIMARY KEY (`ndr_items_id`),
  KEY `idx_ndr_header_fk` (`ndr_id_fk`),
  KEY `idx_ndr_po_items_fk` (`ndr_po_items_id_fk`),
  CONSTRAINT `fk_ndr_items_header` FOREIGN KEY (`ndr_id_fk`) REFERENCES `ndr_tbl` (`ndr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ndr_items_po_ref` FOREIGN KEY (`ndr_po_items_id_fk`) REFERENCES `po_items_tbl` (`po_items_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. Create ndr_items_specs_tbl
CREATE TABLE IF NOT EXISTS `ndr_items_specs_tbl` (
  `ndr_items_spec_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ndr_items_id_fk` bigint unsigned NOT NULL,
  `po_items_spec_id_fk` bigint unsigned DEFAULT NULL,
  `ndr_spec_description` text,
  PRIMARY KEY (`ndr_items_spec_id`),
  KEY `idx_ndr_spec_item_fk` (`ndr_items_id_fk`),
  KEY `idx_ndr_spec_po_spec_fk` (`po_items_spec_id_fk`),
  CONSTRAINT `fk_ndr_specs_item_ref` FOREIGN KEY (`ndr_items_id_fk`) REFERENCES `ndr_items_tbl` (`ndr_items_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ndr_specs_po_ref` FOREIGN KEY (`po_items_spec_id_fk`) REFERENCES `po_items_specs_tbl` (`po_items_spec_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

---

### Backend Models

Create Eloquent Models inside `app/Models` directory:

#### [NEW] [Iar.php](file:///c:/Users/emman/itrac/app/Models/Iar.php) / [IarItem.php](file:///c:/Users/emman/itrac/app/Models/IarItem.php) / [IarItemSpec.php](file:///c:/Users/emman/itrac/app/Models/IarItemSpec.php)
#### [NEW] [Ris.php](file:///c:/Users/emman/itrac/app/Models/Ris.php) / [RisItem.php](file:///c:/Users/emman/itrac/app/Models/RisItem.php) / [RisItemSpec.php](file:///c:/Users/emman/itrac/app/Models/RisItemSpec.php)
#### [NEW] [Rsmi.php](file:///c:/Users/emman/itrac/app/Models/Rsmi.php) / [RsmiItem.php](file:///c:/Users/emman/itrac/app/Models/RsmiItem.php) / [RsmiItemSpec.php](file:///c:/Users/emman/itrac/app/Models/RsmiItemSpec.php)
#### [NEW] [Ics.php](file:///c:/Users/emman/itrac/app/Models/Ics.php) / [IcsItem.php](file:///c:/Users/emman/itrac/app/Models/IcsItem.php) / [IcsItemSpec.php](file:///c:/Users/emman/itrac/app/Models/IcsItemSpec.php)
#### [NEW] [Rspi.php](file:///c:/Users/emman/itrac/app/Models/Rspi.php) / [RspiItem.php](file:///c:/Users/emman/itrac/app/Models/RspiItem.php) / [RspiItemSpec.php](file:///c:/Users/emman/itrac/app/Models/RspiItemSpec.php)
#### [NEW] [Par.php](file:///c:/Users/emman/itrac/app/Models/Par.php) / [ParItem.php](file:///c:/Users/emman/itrac/app/Models/ParItem.php) / [ParItemSpec.php](file:///c:/Users/emman/itrac/app/Models/ParItemSpec.php)
#### [NEW] [Ndr.php](file:///c:/Users/emman/itrac/app/Models/Ndr.php) / [NdrItem.php](file:///c:/Users/emman/itrac/app/Models/NdrItem.php) / [NdrItemSpec.php](file:///c:/Users/emman/itrac/app/Models/NdrItemSpec.php)

#### [MODIFY] [PoParent.php](file:///c:/Users/emman/itrac/app/Models/PoParent.php)
Add relationships to load generated forms:
- `iarReports()`
- `risSlips()`
- `rsmiReports()`
- `icsSlips()`
- `rspiReports()`
- `parReceipts()`
- `ndrReports()`

---

### Routing & Controllers

#### [MODIFY] [web.php](file:///c:/Users/emman/itrac/routes/web.php)
1. Add POST route `/po-review/{po_id}/generate-attachments` to `PoReviewController@generateAttachments`.
2. Update `/delivery-attachment` to `/delivery-attachment/{po_id}` and bind to `DeliveryAttachmentController@showDeliveryAttachment`.

#### [MODIFY] [PoReviewController.php](file:///c:/Users/emman/itrac/app/Http/Controllers/PoReviewController.php)
Implement `generateAttachments($po_id, Request $request)` method:
1. Authenticate & fetch the PO Parent and Items.
2. In a DB Transaction:
   - Loop through items, update their `po_items_category` value.
   - For items categorized as **Supply and Materials**:
     - Generate 1 IAR, then insert IAR items and specification rows.
     - Group quantity distribution by department. Generate 1 RIS per department; insert RIS items and specs.
     - Generate 1 RSMI; insert RSMI items and specs referencing the created RIS slip numbers.
   - For items categorized as **Semi-Expendable**:
     - Generate 1 IAR, then insert IAR items and specification rows.
     - Group quantity distribution by user ID. Generate 1 RIS per user; insert RIS items and specs.
     - Generate 1 ICS per user; insert ICS items and specs.
     - Generate 1 RSPI; insert RSPI items referencing the created ICS slip numbers.
   - For items categorized as **Equipment**:
     - Generate 1 IAR, then insert IAR items and specification rows.
     - Group quantity distribution by user ID. Generate 1 PAR per user; insert PAR items and specs.
   - For items categorized as **Not Delivered**:
     - Generate 1 NDR; insert NDR items and specs.
3. Return JSON: `{ success: true, message: "Delivery Attachments generated successfully.", redirect: "/delivery-attachment/{po_id}" }`.

#### [MODIFY] [DeliveryAttachmentController.php](file:///c:/Users/emman/itrac/app/Http/Controllers/DeliveryAttachmentController.php)
1. Modify `showDeliveryAttachment($po_id)` to find PO Parent.
2. Fetch generated IARs, RISs, RSMIs, ICSs, RSPIs, PARs, and NDRs with their nested items and specs.
3. Pass `$po` and retrieved relationships to the view.

---

### Frontend & Views

#### [MODIFY] [supply-po-review.blade.php](file:///c:/Users/emman/itrac/resources/views/supply/pages/supply-po-review.blade.php)
1. Inject PO ID globally: `<script>window.poId = "{{ $po->po_id }}";</script>`
2. Wrap `#generate-btn` inside container.

#### [MODIFY] [custom-po-review.js](file:///c:/Users/emman/itrac/public/js/supply/po-review/custom-po-review.js)
Bind click handler to `#generate-btn`:
1. Iterate over all `.po-item-row` rows across categorized tables.
2. Extract the selected category, item ID, and distributions (either department name + quantity, or user ID + quantity).
3. Send AJAX JSON POST request to `/po-review/{poId}/generate-attachments`.
4. Render toast on success/failure and redirect.

#### [MODIFY] [supply-delivery-attachment.blade.php](file:///c:/Users/emman/itrac/resources/views/supply/pages/supply-delivery-attachment.blade.php)
1. Replace PHP string markers `$po->po_supplier` with actual blade echo brackets `{{ $po->po_supplier }}`.
2. Update left treeview template:
   - Only render category folders if items exist in that category.
   - List generated files dynamically under folders (e.g. list individual department RIS slips as child items under "Requisition and Issue Slip").
   - Set data attributes (`data-target-id`) on file list elements to link to specific form card IDs.
3. Uncomment and include all `@include` partial templates. Keep them hidden by default via CSS.

#### [NEW] [custom-delivery-attachment.js](file:///c:/Users/emman/itrac/public/js/supply/delivery-attachment/custom-delivery-attachment.js)
1. Implement navigation click handler:
   - When a file item in the treeview is clicked, find the corresponding partial form card.
   - Hide all other cards and display the selected card.
   - Toggle highlighted states on treeview list elements.

---

## Verification Plan

### Automated Tests
*N/A - Manual verification requested.*

### Manual Verification
1. Open the PO Review page, categorize items and distribute them to departments (Supply) and users (Semi-Expendable/Equipment).
2. Set some items as "Not Delivered".
3. Check the confirmation checkbox and click "Generate Delivery Attachment/s".
4. Verify success toast notification and redirection to `/delivery-attachment/{po_id}`.
5. In the Delivery Attachment view, click through the folder hierarchy in the treeview.
6. Verify that selecting an item displays the correct form with populated values, matching the database records created.
