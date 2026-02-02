-- Manually generated migration for Reflection entity
-- Run this on your DB to create the table

CREATE TABLE IF NOT EXISTS reflection (
    id INT AUTO_INCREMENT NOT NULL,
    student_id INT NOT NULL,
    teacher_id INT DEFAULT NULL,
    course_id INT NOT NULL,
    student_text LONGTEXT NOT NULL,
    teacher_response LONGTEXT DEFAULT NULL,
    student_created_at DATETIME NOT NULL,
    teacher_responded_at DATETIME DEFAULT NULL,
    is_read_by_teacher TINYINT(1) NOT NULL,
    is_read_by_student TINYINT(1) NOT NULL,
    INDEX IDX_REFLECTION_STUDENT (student_id),
    INDEX IDX_REFLECTION_TEACHER (teacher_id),
    INDEX IDX_REFLECTION_COURSE (course_id),
    PRIMARY KEY(id),
    CONSTRAINT FK_REFLECTION_STUDENT FOREIGN KEY (student_id) REFERENCES fos_user (id),
    CONSTRAINT FK_REFLECTION_TEACHER FOREIGN KEY (teacher_id) REFERENCES fos_user (id),
    CONSTRAINT FK_REFLECTION_COURSE FOREIGN KEY (course_id) REFERENCES course (id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
