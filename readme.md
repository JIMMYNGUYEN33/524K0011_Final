\# BeePay – E-Wallet Web Application

&#x20;Final Project – 503073 Web Programming and Applications – Semester II/2025-2026





**Group Members**



| Student ID | Full Name            |

|------------|-----------           |

| 524K0011   | Nguyễn Gia Phúc      |

| 524K0010   | Bảo Quý Phúc         |

| 524V0007   | Phan Nguyễn Thảo Như |





**Public Deployed URL**



\--> https://beepay.infinityfreeapp.com



**Login Credentials**



\### Admin Account


 Email:     admin@ewallet.com 

 Password: password 



> The admin account has `is\_first\_login = FALSE` — no forced password change on first login.

> All user accounts start with a balance of \*\*10,000,000 VND\*\* (set by default in the database schema).

**Local Setup Guide (XAMPP)**


**Requirements**



\- \[XAMPP](https://www.apachefriends.org/) with PHP >= 7.4 and MySQL >= 5.7

\- \[Composer](https://getcomposer.org/) installed globally

\- A web browser





**1. Step 1 – Copy the project**



Copy the entire project folder into XAMPP's web root:



C:\\xampp\\htdocs\\524K0011_Final\\



The final path should look like:



C:\\xampp\\htdocs\\524K0011_Final\\auth\\login.php

C:\\xampp\\htdocs\\524K0011_Final\\config\\db\_config.php







**2. Step 2 – Import the database**



1\. Open \*\*XAMPP Control Panel\*\* → start \*\*Apache\*\* and \*\*MySQL\*\*

2\. Open \*\*phpMyAdmin\*\*: \[http://localhost/phpmyadmin](http://localhost/phpmyadmin)

3\. Click the \*\*Import\*\* tab

4\. Click \*\*Choose File\*\* → select \*\*`config/Database.sql`\*\* from the project root

5\. Click \*\*Go\*\*



> The script will automatically create the database `e\_wallet`, all tables, and the admin account. No need to create the database manually first.





**Step 3 – Install Composer dependencies**



Open \*\*Command Prompt\*\*, navigate to the project folder and run:



bash

cd C:\\xampp\\htdocs\\524K0011_Final

composer install





This installs \*\*PHPMailer\*\* (`phpmailer/phpmailer ^7.0`) into the `vendor/` folder.



> `vendor/` is excluded from the Git repository. This step is \*\*required\*\* before the site will work.



\---



**Step 4 – Verify database connection**



Open `config/db\_config.php`. The file auto-detects whether the site is running locally or on the hosted server:





// For localhost (XAMPP default — no changes needed):

$host     = 'localhost';

$dbname   = 'e\_wallet';

$username = 'root';

$password = '';





If your local MySQL uses a different password, update the `$password` value for the `$isLocal` branch.







**5. Step 5 – (Optional) Configure email sending**



Email is already configured with a working Gmail SMTP account in `config/mail.php`. No changes needed to test email features (OTP, registration notification, transfer notification).



If you want to use your own Gmail account, update `config/mail.php`:





return \[

&#x20;   'host'       => 'smtp.gmail.com',

&#x20;   'username'   => 'your\_email@gmail.com',

&#x20;   'password'   => 'your\_16\_char\_app\_password',  // Gmail App Password

&#x20;   'port'       => 587,

&#x20;   'from\_email' => 'your\_email@gmail.com',

&#x20;   'from\_name'  => 'BeePay',

];





> To generate a Gmail App Password: Google Account → Security → 2-Step Verification → App Passwords.





**6. Step 6 – Open the website**



Open your browser and go to:



http://localhost/524K0011_Final/auth/login.php





**Project Structure**



524K0011\_Final/

├── admin/                  # Admin pages (dashboard, account lists, transaction approval)

├── auth/                   # Login, register, logout, forgot password, change password

├── user/                   # User pages: Home, Profile, Deposit, Withdraw, Transfer, History, Buycard, Scan

├── assets/

│   ├── css/                # All stylesheets

│   ├── js/                 # main.js

│   └── upload\_images/      # Static images (carrier logos, etc.)

├── config/

│   ├── Database.sql        # ← Import this to set up the local database

│   ├── db\_config.php       # Auto-detects local vs hosted environment

│   └── mail.php            # PHPMailer / Gmail SMTP config

├── core/

│   ├── index.php           # Alternate entry point

│   ├── WalletBLL.php       # Business logic layer

│   └── WalletDAL.php       # Data access layer

├── helpers/

│   ├── auth.php            # Session management, login guards, base\_url()

│   ├── mailer.php          # send\_mail() wrapper for PHPMailer

│   └── ui.php              # format\_money(), h(), and other UI helpers

├── uploads/                # User-uploaded ID card images (auto-created)

├── vendor/                 # Composer dependencies — run "composer install" to generate

├── composer.json

├── Database\_hosting.sql    # SQL for the hosted version (InfinityFree)

└── README.md







**Test Credit Cards**



\### Deposit

| Card Number | Expiration | CVV | Behaviour |

|------------|------------|-----|-----------|

| 111111 | 10/10/2022 | 411 | Unlimited deposits, any amount |

| 222222 | 11/11/2022 | 443 | Max 1,000,000 VND per transaction |

| 333333 | 12/12/2022 | 577 | Always returns "card is out of money" |



\### Withdrawal

| Card Number | Expiration | CVV |

|------------|------------|-----|

| 111111 | 10/10/2022 | 411 |



\---



**Notes for Grader**



\- \*\*Database name:\*\* `e\_wallet` (not `beepay`). Import `config/Database.sql`, not `Database\_hosting.sql`.

\- \*\*vendor/ folder:\*\* Not in the repository — must run `composer install` first.

\- \*\*uploads/ folder:\*\* Created automatically on first ID card upload. Ensure Apache has write permission to the project directory.

\- \*\*Base URL:\*\* Hardcoded as `/524K0011\_Final` in `helpers/auth.php` → `base\_url()`. The project folder \*\*must\*\* be named `524K0011\_Final` exactly.

\- \*\*Admin password:\*\* The hash in `Database.sql` corresponds to the password `password` (Laravel's default test hash `$2y$10$92IXU...`).

\- \*\*Balance:\*\* All new user accounts default to \*\*10,000,000 VND\*\* as set in the `Users` table schema.



\---

**Demo Video**



> Link: [https://drive.google.com/drive/folders/198MapwoBoCDMRrw83QgJSFq58PRNiDUg?usp=sharing](https://drive.google.com/drive/folders/198MapwoBoCDMRrw83QgJSFq58PRNiDUg?usp=sharing)






**GitHub Repository**



> [https://github.com/JIMMYNGUYEN33/524K0011\_Final](https://github.com/JIMMYNGUYEN33/524K0011_Final.git)

