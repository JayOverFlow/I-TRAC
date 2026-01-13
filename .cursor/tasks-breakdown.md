# Project Tasks Breakdown

This document outlines the key tasks for developing the Procurement and Asset Management system using Laravel, based on the project requirements.

## 1. Core Setup & Authentication
- **1.1 User Authentication:** Implement registration, login, and logout functionalities using Laravel's built-in authentication system.
- **1.2 Dashboard:** Create role-based landing pages that display relevant tasks or summaries for Admin, Head, Procurement Officer, and Supply Officer roles.

## 2. User & Access Control Module (Admin Role)
- **2.1 System Configurations:** Develop interfaces for managing global system settings (Admin only).
- **2.2 User Management:** Implement full CRUD (Create, Read, Update, Delete) operations for user accounts.
- **2.3 Role Management:** Implement full CRUD operations for user roles.
- **2.4 Department/Office Management:** Implement full CRUD operations for departments/offices.
- **2.5 Search & Filter:** Implement global search functionality for users, roles, and departments.

## 3. Procurement Module
- **3.1 Data Import (Head Role):** Develop functionality to import Annual Procurement Plan (APP) files (.xlsx or .csv) into the database. This will likely involve using Laravel Excel (Maatwebsite) or custom CSV parsing.
- **3.2 Purchase Requests (Head Role):** Implement features for Heads to create and manage Purchase Requests (PRs).
- **3.3 Task Assignment (Head Role):** Allow Heads to assign PR tasks to subordinates, making these items visible on the subordinate's task page.
- **3.4 Export PRs (Head Role):** Implement functionality to export PRs with a generated unique tracking code.
- **3.5 PR Processing (Procurement Officer Role):** Create an interface where Procurement Officers can input a unique PR code to load existing request data.
- **3.6 Purchase Orders (Procurement Officer Role):** Develop features for Procurement Officers to create Purchase Orders (POs) based on loaded PR data.
- **3.7 Export POs (Procurement Officer Role):** Implement functionality to export POs with a generated unique tracking code.

## 4. Supply/Asset Module (Supply Officer Role)
- **4.1 Asset/Inventory Management:** Create an interface for Supply Officers to input a PO code to load order details.
- **4.2 Property Acknowledgement Receipts (PAR):** Implement functionality to create Property Acknowledgement Receipts based on loaded PO data.
- **4.3 Inventory Custodian Slips (ICS):** Implement functionality to create Inventory Custodian Slips based on loaded PO data.

## 5. Technical Considerations
- **5.1 Database Design:** Design and implement the necessary MySQL database schema, including tables for users, roles, departments, APPs, PRs, POs, PARs, and ICSs, ensuring proper Eloquent Relationships.
- **5.2 Frontend Development:** Utilize Blade Templates with Tailwind CSS for building the user interface.
- **5.3 Routing:** Define clear and resourceful routes in `web.php` for all application functionalities.
- **5.4 Coding Standards:** Adhere to standard Laravel naming conventions and heavily comment code for clarity and learning.

## 6. Core Process Flow
- Import APP file
- Assign PR
- Create PR
- Submit PR
- Export PR & generate unique code
- Input PR code to load
- Create PO
- Export PO & Generate Unique Code
- Input PO code to load
- Create PAR
- Create ICS
