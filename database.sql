-- ==========================================================
-- Student Notes Sharing Platform
-- Full-Stack Web Application Database Schema & Sample Data
-- BE CSE Web Technology Laboratory
-- Database: student_notes_platform
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `student_notes_platform` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `student_notes_platform`;

-- Disable foreign key checks during schema creation
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- 1. Table structure for table `departments`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table structure for table `subjects`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `department_id` INT NOT NULL,
  `semester` TINYINT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
  INDEX `idx_dept_sem` (`department_id`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table structure for table `users`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `department_id` INT NULL,
  `semester` TINYINT NULL,
  `roll_number` VARCHAR(50) NULL,
  `avatar` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_email` (`email`),
  INDEX `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Table structure for table `notes`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  `semester` TINYINT NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_size` INT NOT NULL,
  `file_type` VARCHAR(50) NOT NULL DEFAULT 'application/pdf',
  `download_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
  INDEX `idx_note_subject` (`subject_id`),
  INDEX `idx_note_dept_sem` (`department_id`, `semester`),
  INDEX `idx_note_downloads` (`download_count`),
  INDEX `idx_note_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table structure for table `bookmarks`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `bookmarks`;
CREATE TABLE `bookmarks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `note_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_note` (`user_id`, `note_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`note_id`) REFERENCES `notes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table structure for table `downloads`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `note_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`note_id`) REFERENCES `notes`(`id`) ON DELETE CASCADE,
  INDEX `idx_download_note` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table structure for table `reports`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `note_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `reason` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`note_id`) REFERENCES `notes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_report_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- SEED DATA INSERTIONS
-- ==========================================================

-- 1. Departments
INSERT INTO `departments` (`id`, `code`, `name`, `description`) VALUES
(1, 'CSE', 'Computer Science & Engineering', 'Focuses on computation, algorithms, software development, data structures, and computer architectures.'),
(2, 'IT', 'Information Technology', 'Covers software systems, networking, cloud infrastructure, and modern information processing.'),
(3, 'ECE', 'Electronics & Communication Engineering', 'Deals with electronic devices, signal processing, VLSI design, and telecommunication systems.'),
(4, 'EEE', 'Electrical & Electronics Engineering', 'Covers power generation, power electronics, transmission, and electrical machinery.'),
(5, 'MECH', 'Mechanical Engineering', 'Encompasses thermodynamics, fluid mechanics, design engineering, and robotics.'),
(6, 'CIVIL', 'Civil Engineering', 'Focuses on structural engineering, geotechnics, environmental engineering, and project management.');

-- 2. Subjects
INSERT INTO `subjects` (`id`, `department_id`, `semester`, `code`, `name`, `description`) VALUES
-- CSE Subjects
(1, 1, 3, 'CS8391', 'Data Structures & Algorithms', 'Stacks, Queues, Linked Lists, Trees, Graphs, Sorting and Dynamic Programming.'),
(2, 1, 4, 'CS8492', 'Database Management Systems', 'Relational models, SQL, Normalization, ACID transactions, and Indexing mechanisms.'),
(3, 1, 5, 'CS8591', 'Computer Networks', 'OSI model, TCP/IP protocol suite, Routing algorithms, and Network security.'),
(4, 1, 6, 'CS8651', 'Internet Programming & Web Tech', 'Client-server architecture, HTML5, CSS3, JavaScript, PHP, and REST APIs.'),
(5, 1, 6, 'CS8691', 'Artificial Intelligence', 'Heuristic search, Knowledge representation, Expert systems, and Machine learning.'),
(6, 1, 7, 'CS8792', 'Cryptography & Network Security', 'Symmetric & Asymmetric Ciphers, RSA, Hash functions, Digital Signatures, and Firewalls.'),
(7, 1, 5, 'CS8501', 'Theory of Computation', 'Automata theory, Regular languages, Context-free grammars, Turing machines, and NP-completeness.'),
(8, 1, 4, 'CS8491', 'Operating Systems', 'Process management, CPU scheduling, Memory virtualization, and File systems.'),

-- IT Subjects
(9, 2, 3, 'IT8301', 'Object Oriented Programming in Java', 'Core OOP concepts, Collections, Multithreading, Exception handling, and Streams.'),
(10, 2, 4, 'IT8402', 'Design and Analysis of Algorithms', 'Divide and conquer, Greedy algorithms, Dynamic programming, and Backtracking.'),
(11, 2, 5, 'IT8501', 'Software Engineering Principles', 'Agile methodologies, UML modeling, Requirement engineering, and Software testing.'),
(12, 2, 6, 'IT8601', 'Cloud Computing Architecture', 'Virtualization, AWS/GCP services, Containerization with Docker, and Serverless architectures.'),

-- ECE Subjects
(13, 3, 3, 'EC8352', 'Signals and Systems', 'Continuous & Discrete time signals, Fourier transform, Laplace and Z-transforms.'),
(14, 3, 4, 'EC8451', 'Electromagnetic Fields', 'Maxwell equations, Wave propagation, Transmission lines, and Waveguides.'),
(15, 3, 5, 'EC8501', 'Digital Communication', 'Pulse modulation, Baseband transmission, Passband data transmission, and Error coding.'),
(16, 3, 6, 'EC8691', 'Microprocessors & Microcontrollers', '8086 architecture, 8051 microcontroller, Interfacing peripherals, and Assembly programming.'),

-- EEE Subjects
(17, 4, 3, 'EE8351', 'Electric Circuits Analysis', 'Mesh and Nodal analysis, AC network theorems, Resonance, and Transient analysis.'),
(18, 4, 5, 'EE8501', 'Power System Analysis', 'Load flow studies, Symmetrical and Unsymmetrical faults, and Power system stability.'),

-- MECH Subjects
(19, 5, 3, 'ME8391', 'Engineering Thermodynamics', 'Laws of thermodynamics, Air standard cycles, Steam power plants, and Refrigeration cycles.'),
(20, 5, 4, 'ME8492', 'Kinematics of Machinery', 'Mechanisms, Cams, Gear trains, and Velocity/Acceleration analysis.'),

-- CIVIL Subjects
(21, 6, 3, 'CE8301', 'Strength of Materials', 'Stress and strain, Shear force and bending moment diagrams, Torsion, and Deflection of beams.'),
(22, 6, 4, 'CE8402', 'Fluid Mechanics & Machinery', 'Fluid properties, Bernoulli equation, Pipe flow, Pumps, and Turbines.');

-- 3. Seed users are disabled until setup.php assigns unique generated passwords.
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `department_id`, `semester`, `roll_number`) VALUES
(1, 'System Administrator', 'admin@notesharing.edu', '!disabled!', 'admin', 1, 8, 'ADM-001'),
(2, 'Rahul Sharma', 'rahul.cse@notesharing.edu', '!disabled!', 'student', 1, 6, '21CS1042'),
(3, 'Priya Nair', 'priya.it@notesharing.edu', '!disabled!', 'student', 2, 6, '21IT1018'),
(4, 'Arjun Verma', 'arjun.ece@notesharing.edu', '!disabled!', 'student', 3, 5, '22EC1077');

-- 4. Sample Notes
INSERT INTO `notes` (`id`, `user_id`, `subject_id`, `department_id`, `semester`, `academic_year`, `title`, `description`, `file_path`, `original_filename`, `file_size`, `file_type`, `download_count`, `created_at`) VALUES
(1, 2, 2, 1, 4, '2024-2025', 
 'Complete DBMS Unit 1 to 5 Comprehensive Revision Notes', 
 'Covers Relational Algebra, ER Models, SQL Queries, Normal Forms (1NF through BCNF), Transaction Processing, Concurrency Control, and Recovery protocols. Includes university previous year exam questions with solved solutions.', 
 'sample_dbms_unit1_5.pdf', 'DBMS_Comprehensive_Notes.pdf', 3145728, 'application/pdf', 142, NOW() - INTERVAL 12 DAY),

(2, 2, 4, 1, 6, '2024-2025', 
 'Web Technology Lab Guide & PHP Full-Stack Architecture Notes', 
 'In-depth notes on Client-Server Architecture, HTTP protocols, PHP 8 Sessions, PDO Prepared Statements, RESTful APIs, Form validation, and responsive Bootstrap layouts with code samples.', 
 'sample_web_technology_lab.pdf', 'Web_Tech_Lab_Guide.pdf', 2411724, 'application/pdf', 98, NOW() - INTERVAL 9 DAY),

(3, 2, 1, 1, 3, '2023-2024', 
 'Data Structures & Algorithms - Trees and Graph Algorithms Visualized', 
 'Clear diagrams and pseudocode for AVL Trees, Red-Black Trees, BFS/DFS traversals, Dijkstra Shortest Path, Prim/Kruskal MST, and Topological Sort with asymptotic time complexity analyses.', 
 'sample_dsa_trees_graphs.pdf', 'DSA_Trees_Graphs.pdf', 1887436, 'application/pdf', 215, NOW() - INTERVAL 20 DAY),

(4, 3, 10, 2, 4, '2024-2025', 
 'Design & Analysis of Algorithms - Dynamic Programming Formula Sheet', 
 'Condensed summary of 0/1 Knapsack, Longest Common Subsequence, Matrix Chain Multiplication, Floyd-Warshall, and Bellman-Ford algorithms with recurrence relations and trace tables.', 
 'sample_algorithms_dp_sheet.pdf', 'DAA_DP_Formula_Sheet.pdf', 1450210, 'application/pdf', 76, NOW() - INTERVAL 7 DAY),

(5, 3, 12, 2, 6, '2024-2025', 
 'Cloud Computing - Docker, Kubernetes & AWS Core Services Summary', 
 'Architectural overview of Cloud Service Models (IaaS, PaaS, SaaS), Docker containerization, Kubernetes pods and deployments, AWS EC2, S3, IAM, and Lambda serverless setups.', 
 'sample_cloud_computing_aws.pdf', 'Cloud_Computing_Guide.pdf', 2854912, 'application/pdf', 64, NOW() - INTERVAL 5 DAY),

(6, 4, 13, 3, 3, '2023-2024', 
 'Signals & Systems - Fourier Series and Z-Transform Solved Problems', 
 'Step-by-step mathematical derivations for Continuous-Time Fourier Series, Discrete-Time Fourier Transform, ROC properties in Z-domain, and convolution integrals.', 
 'sample_signals_and_systems.pdf', 'Signals_Systems_Derivations.pdf', 2097152, 'application/pdf', 110, NOW() - INTERVAL 15 DAY),

(7, 4, 16, 3, 6, '2024-2025', 
 'Microprocessors 8086 Assembly Programming and Peripheral Interfacing', 
 'Pin diagram details, 8086 addressing modes, instruction set reference, and peripheral interfacing notes for 8255 PPI, 8259 PIC, and 8254 Timer with sample assembly programs.', 
 'sample_microprocessors_8086.pdf', 'Microprocessor_8086_Guide.pdf', 2621440, 'application/pdf', 53, NOW() - INTERVAL 3 DAY),

(8, 2, 6, 1, 7, '2024-2025', 
 'Cryptography & Network Security - Modern Ciphers & Hash Functions', 
 'Comprehensive guide to AES, DES, RSA public-key crypto, Diffie-Hellman Key Exchange, SHA-256, HMAC, and SSL/TLS handshake mechanisms.', 
 'sample_cryptography_security.pdf', 'Cryptography_Network_Security.pdf', 3407872, 'application/pdf', 88, NOW() - INTERVAL 2 DAY);

-- 5. Sample Bookmarks
INSERT INTO `bookmarks` (`user_id`, `note_id`, `created_at`) VALUES
(2, 4, NOW() - INTERVAL 6 DAY),
(2, 5, NOW() - INTERVAL 4 DAY),
(3, 1, NOW() - INTERVAL 8 DAY),
(3, 2, NOW() - INTERVAL 7 DAY),
(4, 1, NOW() - INTERVAL 10 DAY),
(4, 3, NOW() - INTERVAL 12 DAY);

-- 6. Sample Downloads Log
INSERT INTO `downloads` (`user_id`, `note_id`, `ip_address`, `user_agent`, `downloaded_at`) VALUES
(2, 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', NOW() - INTERVAL 6 DAY),
(3, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', NOW() - INTERVAL 8 DAY),
(4, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', NOW() - INTERVAL 4 DAY),
(3, 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', NOW() - INTERVAL 11 DAY);

-- 7. Sample Reports
INSERT INTO `reports` (`note_id`, `user_id`, `reason`, `details`, `status`, `created_at`) VALUES
(6, 2, 'Wrong Semester Tag', 'Note is labeled as Semester 3 but our syllabus teaches Signals & Systems in Semester 4.', 'pending', NOW() - INTERVAL 1 DAY);
