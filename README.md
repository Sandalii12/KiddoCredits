PROJECT STRUCTURE
KiddoCredits/
│
├── assets/
│   ├── logo.png
│   └── icons/           (optional)
│
├── css/
│   ├── style.css
│   ├── parent.css
│   ├── child.css
│   └── login.css
│
├── js/
│   ├── main.js
│   ├── timer.js         (countdown for child tasks)
│   └── validation.js
│
├── includes/
│   ├── db_connection.php
│   ├── header_parent.php
│   ├── header_child.php
│   ├── footer.php
│   └── auth_session.php   (to check login session)
│
├── parent/
│   ├── dashboard.php
│   ├── add_child.php
│   ├── task_assign.php
│   ├── task_list.php
│   ├── reward_add.php
│   ├── reward_list.php
│   └── logout.php
│
├── child/
│   ├── dashboard.php
│   ├── tasks.php
│   ├── completed_tasks.php
│   ├── reward_catalogue.php
│   └── logout.php
│
├── auth/
│   ├── login.php
│   ├── signup_parent.php
│   └── logout.php
│
└── index.php
