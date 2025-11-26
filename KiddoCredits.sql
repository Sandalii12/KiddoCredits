-- -----------------------------------------------------
-- Database: KiddoCredits
-- -----------------------------------------------------

CREATE DATABASE IF NOT EXISTS KiddoCredits;
USE KiddoCredits;

-- -----------------------------------------------------
-- Table: Parent
-- -----------------------------------------------------
CREATE TABLE Parent (
    parent_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_name VARCHAR(100) NOT NULL,
    parent_username VARCHAR(100) NOT NULL UNIQUE,
    parent_password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table: Child
-- -----------------------------------------------------
CREATE TABLE Child (
    child_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    child_name VARCHAR(100) NOT NULL,
    child_username VARCHAR(100) NOT NULL UNIQUE,
    child_password VARCHAR(255) NOT NULL,
    child_points INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES Parent(parent_id)
        ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: Task
-- -----------------------------------------------------
CREATE TABLE Task (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    child_id INT NOT NULL,
    task_title VARCHAR(150) NOT NULL,
    task_desc TEXT,
    task_points INT NOT NULL,
    task_duedate DATE NOT NULL,
    task_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES Parent(parent_id)
        ON DELETE CASCADE,
        
    FOREIGN KEY (child_id) REFERENCES Child(child_id)
        ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: Reward
-- -----------------------------------------------------
CREATE TABLE Reward (
    reward_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    child_id INT,
    reward_title VARCHAR(150) NOT NULL,
    reward_desc TEXT,
    reward_cost INT NOT NULL,
    reward_category VARCHAR(50),
    reward_status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES Parent(parent_id)
        ON DELETE CASCADE,
        
    FOREIGN KEY (child_id) REFERENCES Child(child_id)
        ON DELETE SET NULL
);

-- -----------------------------------------------------
-- Table: Redeemed
-- -----------------------------------------------------
CREATE TABLE Redeemed (
    redeemed_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    parent_id INT NOT NULL,
    reward_id INT NOT NULL,
    cost INT NOT NULL,                 -- cost at the time of redemption
    redeemed_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (child_id) REFERENCES Child(child_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES Parent(parent_id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES Reward(reward_id) ON DELETE CASCADE
);

