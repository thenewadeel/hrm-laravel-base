# **Screen Requirements and Design Specification**

## **1\. Introduction**

This document provides a detailed breakdown of the screens and forms required to support the functional requirements of the SaaS application. The design principles are centered on modularity, clean user experience, and code reusability, leveraging the capabilities of Livewire, Tailwind CSS, and Alpine.js. Each screen is specified with its purpose, key components, and a high-level user workflow.

## **2\. General UI and Architectural Principles**

-   **Modular Components:** Screens will be composed of reusable Livewire components (e.g., data tables, forms, modals) to promote code reusability and simplify maintenance.
-   **Consistent Design:** All screens will follow a consistent design language using Tailwind CSS, ensuring a uniform and professional look and feel.
-   **API-Driven:** All data displayed on the screens will be fetched asynchronously from the API via a dedicated service, ensuring a clear separation between the frontend and backend.
-   **Real-time Updates:** Livewire's reactivity will be used to provide a seamless, real-time user experience for data updates and interactions.

## **3\. Core Authentication Screens**

### **3.1 Login Screen**

-   **Purpose:** To allow users to securely log in to the application.
-   **Key Components:**
    -   Input fields for **Email/Username** and **Password**.
    -   A "Remember me" checkbox.
    -   A link to the **"Forgot Password"** page.
    -   A **"Login" button** that submits credentials to the API.
-   **User Workflow:** User enters credentials, clicks login. Livewire handles the form submission, validation, and redirects the user upon success.

### **3.2 Registration Screen**

-   **Purpose:** For new users to create an account within a new or existing organization.
-   **Key Components:**
    -   Input fields for **Name**, **Email**, and **Password** (with confirmation).
    -   A checkbox for agreeing to terms and conditions.
    -   A **"Register" button**.
-   **User Workflow:** User fills out registration details, submits the form, and is redirected to their respective dashboard upon successful account creation.

## **4\. Accounts Department Screens**

### **4.1 Accounts Dashboard**

-   **Purpose:** The main landing page for the Accounts Department, providing a high-level financial overview.
-   **Key Components:**
    -   **Summary Cards:** Large, clean cards displaying key metrics like **Bank Balance**, **Total Sales (MTD)**, **Total Expenses (MTD)**, and **Outstanding Receivables**.
    -   **Quick-Action Buttons:** Prominent buttons for common tasks such as **"Create New Voucher,"** **"View Trial Balance,"** and **"Manage Ledgers."**
    -   **Recent Activity Feed:** A scrollable list of recently posted vouchers, pending approvals, and account adjustments.
    -   **Chart Component:** A visual chart showing monthly sales vs. expenses to provide a trend overview.

### **4.2 Voucher Management Screens**

#### **4.2.1 Voucher List**

-   **Purpose:** A master screen for viewing, filtering, and managing all vouchers.
-   **Key Components:**
    -   A **Livewire Data Table component** that is sortable and searchable. It will display columns for Voucher ID, Type, Date, Amount, Customer/Vendor, and Status.
    -   **Advanced Filters:** A sidebar or dropdown with filters for date range, voucher type (Sales, Purchase, Salary, etc.), and posting status (Draft, Posted).
    -   Action buttons on each row for **viewing, editing, and posting** the voucher.

#### **4.2.2 Voucher Creation/Edit Form**

-   **Purpose:** A single, dynamic screen for creating and editing all types of vouchers. The form's fields and validation will change based on the selected voucher type.
-   **Key Components:**
    -   A dropdown for selecting the **Voucher Type**.
    -   **Standard fields:** Date, Reference Number, Description.
    -   **Line Items Component:** A reusable Livewire component that allows for adding multiple ledger entries. Each line item will have fields for **Account (Ledger)**, **Description**, and **Amount**. The component will automatically calculate the total debits and credits.
    -   **Type-specific fields:**
        -   **Sales/Purchase:** Dropdown for selecting a customer or vendor ledger.
        -   **Salary/Expense:** A searchable dropdown to select the employee or expense account.
    -   Buttons to **"Save as Draft"** and **"Post Voucher."** A confirmation modal will appear upon clicking "Post."

### **4.3 Financial Reporting Screens**

-   **Purpose:** Dedicated, read-only screens for generating and displaying detailed financial reports.
-   **Key Components:**
    -   A **Date Range Selector** at the top of the page.
    -   A **Livewire Report Table Component** that displays the report data in a robust, read-only table.
    -   **"Export to PDF"** and **"Export to CSV"** buttons.
    -   A collapsible section showing **Report Parameters** (e.g., specific accounts included).
-   **Specific Screens:**
    -   **Trial Balance Report:** Displays all ledger balances.
    -   **Balance Sheet Report:** Shows assets, liabilities, and equity.
    -   **Profit and Loss Statement:** Summarizes revenues and expenses over a period.
    -   **Bank Statement:** A detailed view of transactions for a selected bank account.

### **4.4 Accounting Operations**

-   **Chart of Accounts Management:**
    -   **Purpose:** A screen for viewing, adding, and editing the hierarchical Chart of Accounts.
    -   **Key Components:**
        -   A tree-view component to display the nested account hierarchy.
        -   An **"Add Account"** button that opens a modal form.
        -   A search bar for quickly finding specific accounts.

## **5\. Human Resources Department Screens**

### **5.1 HR Dashboard**

-   **Purpose:** The main hub for HR administrators.
-   **Key Components:**
    -   **Summary Cards:** Displaying metrics such as **Total Employees**, **Active Leave Requests**, and **Upcoming Employee Anniversaries**.
    -   **Quick Links:** Buttons for **"Add New Employee,"** **"Manage Payroll,"** and **"Approve Leave."**
    -   **Pending Leave Requests Table:** A small, live-updating table showing all pending leave requests with "Approve" and "Reject" actions.

### **5.2 Employee Management Screens**

-   **Employee List:**
    -   **Purpose:** To provide a comprehensive, searchable list of all employees.
    -   **Key Components:**
        -   A **Livewire Data Table component** with columns for Employee ID, Name, Job Title, Department, and Status.
        -   **Search and Filter inputs** to find employees quickly.
        -   An "Add New Employee" button that leads to the creation form.
-   **Employee Profile:**
    -   **Purpose:** A detailed, multi-tabbed view of a single employee's information.
    -   **Key Components:**
        -   **Tabbed Interface:**
            -   **Personal Details:** Name, contact, address, joining date.
            -   **Payroll & Compensation:** Salary details, allowances, deductions, and tax information.
            -   **Leave History:** A list of all past leave applications and balances.
            -   **Loan Details:** Records of any outstanding loans.
            -   **Documents:** Uploaded employee documents.

### **5.3 Payroll Management**

-   **Payroll Generation Screen:**
    -   **Purpose:** To run payroll for a specific period and manage salary calculations.
    -   **Key Components:**
        -   A date selector for the payroll period.
        -   A table listing employees with their base salary, calculated allowances, deductions, and the final net pay.
        -   A "Generate Pay Slips" button that triggers the API call and generates documents.

### **5.4 Leave Management**

-   **Leave Request List (HR):**
    -   **Purpose:** To manage and approve employee leave requests.
    -   **Key Components:**
        -   A **Livewire Data Table component** showing pending leave requests.
        -   Action buttons on each row to **"Approve"** or **"Reject"** the request, with a comment field for rejection reasons.

## **6\. General Employee Screens**

### **6.1 Employee Dashboard**

-   **Purpose:** A personalized dashboard for general employees.
-   **Key Components:**
    -   A summary of their current **leave balance**.
    -   A list of their **recent pay slips** (last 3-5).
    -   A **"Request Leave"** quick-action button.
    -   A **"View Profile"** button to see their own details.

### **6.2 Pay Slips and Leave**

-   **Purpose:** A dedicated screen for employees to access their documents and manage leave.
-   **Key Components:**
    -   A tabbed interface with:
        -   **Pay Slips:** A list of all historical pay slips with a "Download PDF" button for each.
        -   **Leave Management:** A form for submitting new leave requests and a table to view the status of past requests.

## **7\. System Administration & Settings Screens**

### **7.1 User Profile Management**

-   **Purpose:** To allow users to manage their own personal information and security settings.
-   **Key Components:**
    -   A **profile form** for updating personal details (name, contact information).
    -   A section for changing the user's **password**.
    -   A component for managing **Two-Factor Authentication (2FA)** settings.

### **7.2 Organization Settings**

-   **Purpose:** To allow administrators to configure company-wide settings.
-   **Key Components:**
    -   **General Settings Form:** Fields for updating the organization's name, logo, and contact information.
    -   **Financial Year Management:** A form to set and change the financial year, including opening and closing dates.
    -   **User Roles and Permissions:** A component to view and edit predefined roles and their associated permissions. This provides a central place to control user access.

### **7.3 User Management**

-   **Purpose:** To allow an administrator or HR staff to manage all users within the organization.
-   **Key Components:**
    -   A **Livewire Data Table** component listing all users, with columns for Name, Email, Role, and Status.
    -   **Search and Filter functionality** to find users quickly.
    -   **Actions:** On each user's row, an administrator can click to **edit their profile**, **reset their password**, or **change their role/status**.
    -   An **"Add New User" button** that opens a modal for creating a new user account.

## **8\. Reusable Core Components**

These components are foundational to the application's clean and robust design.

-   **Livewire Data Table Component:** A generic component that accepts data and column definitions. It will handle sorting, searching, and pagination automatically, reducing boilerplate code.
-   **Livewire Dynamic Form Component:** A component that can generate different forms based on a provided configuration array. It will handle validation, data binding, and can be used for both creation and editing.
-   **Modal/Dialog Component:** A reusable component for pop-ups, such as a confirmation message before posting a voucher or a quick view of a ledger's details.
-   **Notification Component:** A non-intrusive, dismissible component for displaying success, error, and warning messages to the user.
