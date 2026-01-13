# project-requirements

Project Requirements & Learning Guidelines
1. Developer Context & Learning Goals
Crucial Context for AI Assistants:
- User Level: Beginner Developer.
- Primary Goal: To learn the basics and fundamentals of the Laravel framework while building this application.
- Coding Constraints:
 	- Keep it Simple: Do not offer advanced architectural patterns (like Repository Pattern, DDD, or complex Service layers). Stick to standard MVC (Model-View-Controller).
    - Explanation Over Speed: Explain the "why" behind Laravel fundamentals (Routing, Middleware, Eloquent, Blade).
    - Avoid Overwhelm: Break down tasks into small, manageable steps. Use explicit logic over "magic" code.

2. Project Overview
A web-based Procurement and Asset Management system built with Laravel. The application handles the workflow from Annual Procurement Plans (APP) and Purchase Requests (PR) to Purchase Orders (PO) and Property Acknowledgement (PAR/ICS).

3. Functional Requirements
3.1 Core Features
User Authentication: Registration, Login, and Logout using standard Auth.
Dashboard: Role-based landing pages showing relevant tasks or summaries.
Search & Filter: Global search functionality for users, roles, and departments.

3.2 User Roles & Functions
Admin (System Administrator)
- System Configurations: Manage global settings.
- User Management: Full CRUD (Create, Read, Update, Delete) for User accounts.
- Role Management: Full CRUD for User Roles.
- Department/Office Management: Full CRUD for Departments/Offices.
- Search: Advanced search to filter by user, role, or department.
Head (Department/Office Head)
Data Import: Import Annual Procurement Plan (APP) files (.xlsx or .csv) into the database.
Purchase Requests (PR):
- Create and manage PRs.
- Assign PR tasks to subordinates (items appear on the subordinate's task page).
- Export PRs with a generated unique tracking code.
Procurement Officer
- PR Processing: Input a unique PR code to load existing request data.
- Purchase Orders (PO):
- Create POs based on loaded PR data.
- Export POs with a generated unique tracking code.
Supply Officer
Asset/Inventory Management:
- Input PO code to load order details.
- Create Property Acknowledgement Receipts (PAR).
- Create Inventory Custodian Slips (ICS).

3.3 Specific Modules
- User & Access Control: Users, Roles, Permissions, Departments.
- Procurement Module: APP Imports, Purchase Requests, Task Assignments.
- Finance/Procurement Module: Purchase Orders.
- Supply/Asset Module: PAR and ICS documents.

4. Tech Stack
- Framework: Laravel (Latest Stable Version)
- Language: PHP
- Database: MySQL
- Frontend: Blade Templates with Tailwind CSS
- File Handling: Laravel Excel (Maatwebsite) or simple CSV parsing for imports.

5. Coding Standards for this Project
- Naming Conventions: Standard Laravel conventions (e.g., PurchaseRequestController, Department model).
- Comments: Heavily comment code to explain logic for learning purposes.
- Logic: Use Eloquent Relationships (one-to-many, etc.) to link Users to Departments and PRs to POs.
- Routes: Use clear, resourceful routing in web.php.

6. Core Process
Import APP file->Assign PR->Create PR->Submit PR->Export PR & generate unique code->Input PR code to load->Create PO->Export PO & Generate Unique Code->Input PO code to load->Create PAR->Create ICS

