| Column                | Type                | Attributes                                    | Null | Default            | Extra                | Comment                                 |
|----------------------|---------------------|-----------------------------------------------|------|--------------------|----------------------|-----------------------------------------|
| id                   | BIGINT UNSIGNED     | Primary Key, Auto Increment                   | No   | NULL               | Auto Increment       | Primary key for users                    |
| name                 | VARCHAR(255)        |                                               | No   | NULL               |                      | User full name                           |
| email                | VARCHAR(255)        | UNIQUE                                        | Yes  | NULL               |                      | User email address                       |
| password             | VARCHAR(255)        |                                               | No   | NULL               |                      | Encrypted user password                  |
| phone                | VARCHAR(20)         |                                               | Yes  | NULL               |                      | User phone number                        |
| address              | VARCHAR(255)        |                                               | Yes  | NULL               |                      | User home address                        |
| role                 | ENUM('admin','merchant','customer') |                          | No   | 'customer'          |                      | Defines user type                        |
| status               | ENUM('active','inactive','pending') |                        | No   | 'pending'           |                      | Account status                           |
| language             | VARCHAR(255)        |                                               | No   | 'ar'               |                      | User's preferred language                |
| currency             | VARCHAR(255)        |                                               | No   | 'ILS'              |                      | Default currency (e.g., شيكل)               |
| theme                | VARCHAR(255)        |                                               | No   | 'light'            |                      | UI Theme                                 |
| notification_methods | JSON                |                                               | Yes  | NULL               |                      | Notification channel preferences         |
| recive_notification  | BOOLEAN             |                                               | No   | true               |                      | User receives notifications?             |
| share_location       | BOOLEAN             |                                               | No   | false              |                      | User shares location?                    |
| city_id              | BIGINT UNSIGNED     | Foreign Key -> cities.id                      | Yes  | NULL               |                      | Associated city                          |
| remember_token       | VARCHAR(100)        |                                               | Yes  | NULL               |                      | Session persistence                      |
| deleted_at           | TIMESTAMP           | Soft Deletes                                 | Yes  | NULL               |                      | Soft delete timestamp                    |
| created_at           | TIMESTAMP           |                                               | Yes  | CURRENT_TIMESTAMP  |                      | Date of creation                         |
| updated_at           | TIMESTAMP           |                                               | Yes  | NULL               |                      | Last update date                         |
