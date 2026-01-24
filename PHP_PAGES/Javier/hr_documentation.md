# Placeholder documentation
# hr pages
hr_actions_management.php is the frontend
hr_actions.php has all the CRUD functions
hr_actions.css is the css for the frontend

# OWASP
hr pages is protected from injection by using prepared statements in hr_actions.php

hr pages is protected from Broken Access Control. Need to login to an authorised account to access the page and for some actions

Unauthenicated users should be redirected to login.php
All accounts except for employee can access hr_actions_management.php

If you need to logout go to logout.php from the url or click the logout button

# project
login.php is a placeholder and isn't connected to the project_database.sql
the RBAC should still work when making a new login.php later. Don't need change hr_actions_management.php anymore.

# Later documentation
For each vulnerability addressed, you must conduct
a risk analysis explaining how the vulnerability could impact your specific
application, including potential attack vectors and consequences, then
implement and document appropriate security controls with code
examples. You must verify your mitigations work using appropriate testing
tools such as OWASP ZAP or Burp Suite, documenting the test
procedures, results, and justify your security approach based on best
practices. Your submission will be evaluated based on the difficulty level
of vulnerabilities addressed and the thoroughness of your security analysis
and implementation across the team.