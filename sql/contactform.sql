CREATE DATABASE DI_ContactForm;

use DI_ContactForm;

CREATE TABLE form_submissions (
                                  messageid int(11) NOT NULL auto_increment,
                                  fullname varchar(255) NOT NULL,
                                  email varchar(255) NOT NULL,
                                  phone varchar(255) NULL,
                                  message  text NOT NULL,
                                  submitted TIMESTAMP,
                                  PRIMARY KEY (messageid)
);

CREATE USER 'diuser'@'*' IDENTIFIED BY 'd1Ch@allenge';
GRANT ALL PRIVILEGES ON DI_ContactForm.* TO 'diuser'@'*';
FLUSH PRIVILEGES;
