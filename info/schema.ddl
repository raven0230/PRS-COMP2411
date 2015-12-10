CREATE TABLE Area (
  id   INT         NOT NULL,
  name VARCHAR(20) NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON Area (id);
CREATE TABLE Author (
  id      INT          NOT NULL,
  name    VARCHAR(100) NOT NULL,
  address TEXT(65535)  NOT NULL,
  city    VARCHAR(100) NOT NULL,
  country VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX Author_name_uindex ON Author (name);
CREATE TABLE Author_Paper (
  author_id INT NOT NULL,
  paper_id  INT NOT NULL
);
CREATE INDEX author_id ON Author_Paper (author_id);
CREATE INDEX Author_Paper_Paper__fk ON Author_Paper (paper_id);
CREATE TABLE Conference_Chair (
  title      VARCHAR(100) NOT NULL,
  first_name VARCHAR(50)  NOT NULL,
  last_name  VARCHAR(50)  NOT NULL,
  phone      VARCHAR(15)  NOT NULL,
  fax        VARCHAR(15)  NOT NULL,
  department VARCHAR(50)  NOT NULL,
  gender     TINYINT      NOT NULL,
  password   VARCHAR(20)  NOT NULL,
  email      VARCHAR(50)  NOT NULL,
  id         INT          NOT NULL,
  PRIMARY KEY (id)
);
CREATE TABLE Conference_Chair_Organisation (
  organisation_id     INT NOT NULL,
  conference_chair_id INT NOT NULL
);
CREATE INDEX Conference_Chair_Organisation_Conference_Chair__fk ON Conference_Chair_Organisation (conference_chair_id);
CREATE INDEX organisation_id ON Conference_Chair_Organisation (organisation_id);
CREATE TABLE Keyword (
  id      INT         NOT NULL,
  keyword VARCHAR(15) NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON Keyword (id);
CREATE UNIQUE INDEX unique_keyword ON Keyword (keyword);
CREATE TABLE Paper (
  id                INT          NOT NULL,
  title             VARCHAR(150) NOT NULL,
  status            TINYINT,
  responsible_chair INT          NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON Paper (id);
CREATE TABLE Paper_Keyword (
  paper_id   INT,
  keyword_id INT
);
CREATE INDEX keyword_id ON Paper_Keyword (keyword_id);
CREATE INDEX paper_id ON Paper_Keyword (paper_id);
CREATE TABLE Review_Record (
  id             INT         NOT NULL,
  file           LONGBLOB,
  submission_id  INT         NOT NULL,
  reviewer_id    VARCHAR(50) NOT NULL,
  file_mime      VARCHAR(20),
  rating         INT,
  assigned_time  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_time TIMESTAMP,
  completed      TINYINT,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON Review_Record (id);
CREATE INDEX Review_Record_ratings__fk ON Review_Record (rating);
CREATE INDEX reviewer_id ON Review_Record (reviewer_id);
CREATE INDEX submission_id ON Review_Record (submission_id);
CREATE TABLE Reviewer (
  title      VARCHAR(100) NOT NULL,
  first_name VARCHAR(50)  NOT NULL,
  last_name  VARCHAR(50)  NOT NULL,
  phone      VARCHAR(15)  NOT NULL,
  fax        VARCHAR(15)  NOT NULL,
  department VARCHAR(50)  NOT NULL,
  gender     TINYINT      NOT NULL,
  address    TEXT(65535)  NOT NULL,
  city       VARCHAR(100) NOT NULL,
  country    VARCHAR(50)  NOT NULL,
  password   VARCHAR(20)  NOT NULL,
  deleted    TINYINT DEFAULT 0,
  email      VARCHAR(50)  NOT NULL,
  id         INT          NOT NULL,
  PRIMARY KEY (id)
);
CREATE TABLE Reviewer_Area (
  area_id     INT NOT NULL,
  reviewer_id INT
);
CREATE INDEX area_id ON Reviewer_Area (area_id);
CREATE INDEX Reviewer_Area_Reviewer__fk ON Reviewer_Area (reviewer_id);
CREATE TABLE Reviewer_Organisation (
  organisation_id INT NOT NULL,
  reviewer_id     INT NOT NULL
);
CREATE INDEX organisation_id ON Reviewer_Organisation (organisation_id);
CREATE INDEX Reviewer_Organisation_Reviewer__fk ON Reviewer_Organisation (reviewer_id);
CREATE TABLE Submission (
  id           INT         NOT NULL,
  reviewStatus TINYINT     NOT NULL,
  type         TINYINT     NOT NULL,
  "time"       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  file         BLOB        NOT NULL,
  file_mime    VARCHAR(20) NOT NULL,
  paper_id     INT         NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON Submission (id);
CREATE INDEX Submission_Paper__fk ON Submission (paper_id);
CREATE TABLE Track_Chair (
  title      VARCHAR(100) NOT NULL,
  first_name VARCHAR(50)  NOT NULL,
  last_name  VARCHAR(50)  NOT NULL,
  phone      VARCHAR(15)  NOT NULL,
  fax        VARCHAR(50)  NOT NULL,
  department VARCHAR(50)  NOT NULL,
  gender     TINYINT      NOT NULL,
  address    TEXT(65535)  NOT NULL,
  password   VARCHAR(20)  NOT NULL,
  id         INT          NOT NULL,
  email      VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id)
);
CREATE TABLE Track_Chair_Area (
  area_id        INT NOT NULL,
  track_chair_id INT NOT NULL
);
CREATE INDEX area_id ON Track_Chair_Area (area_id);
CREATE INDEX Track_Chair_Area_Track_Chair__fk ON Track_Chair_Area (track_chair_id);
CREATE TABLE Track_Chair_Organisation (
  organisation_id INT NOT NULL,
  track_chair_id  INT NOT NULL
);
CREATE INDEX organisation_id ON Track_Chair_Organisation (organisation_id);
CREATE INDEX Track_Chair_Organisation_Track_Chair__fk ON Track_Chair_Organisation (track_chair_id);
CREATE TABLE conference_manager (
  email    VARCHAR(50) NOT NULL,
  password VARCHAR(20) NOT NULL
);
CREATE TABLE organisation (
  id   INT         NOT NULL,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
);
CREATE UNIQUE INDEX unique_id ON organisation (id);
CREATE TABLE ratings (
  rating INT NOT NULL,
  PRIMARY KEY (rating)
);
CREATE TABLE time_tester (
  "time" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);