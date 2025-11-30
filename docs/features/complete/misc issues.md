## Issue: setup controller causing issues in step 2 (store creation)

redirects back to store creation page...
was resolved by fresh seeding od db.

-   long term solution: admin level prtal acces for handling such user organization attchment issues

## routing issues

-   organizations lands up in api organizations route...
-   i think other routes are also bleeding into api routes

## Views not found

-   hr.shifts.show
-   payroll.dashboard

## Issues in views

### hr - employees index

-   table not clickable
-   add employee button not working
-   organization units not mentioned
-   needs more details
-   badges.... we love badges ( maybe an app wide badge system...)

### hr - employee show

-   payroll details missing or perhaps no data is present
-   edit button leads to error ( Call to undefined method App\Http\Controllers\HR\EmployeeController::edit() )
-   long scientific number in years of service
-   needs more colors

### attendance dashboard http://localhost:8000/attendance/dashboard

-   sync and payroll buttons not working
-   Attendance Records table needs filters and search
-   employee select dropdown is useless, shows one entry 'all employees' only. perhaps a new UI component for selecting such model filters...

### $slot issues in various accounting pages

http://localhost:8000/accounts/cash-payments and such show errors
