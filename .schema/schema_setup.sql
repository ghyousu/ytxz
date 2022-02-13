----------------------------------- ytxz schema ------------------------------
DROP TABLE IF EXISTS ytxz.history        CASCADE;
DROP TABLE IF EXISTS ytxz.favorite       CASCADE;
DROP TABLE IF EXISTS ytxz.users          CASCADE;
DROP TABLE IF EXISTS ytxz.user_type_enum CASCADE;
DROP TABLE IF EXISTS ytxz.file_type_enum CASCADE;
DROP TABLE IF EXISTS ytxz.quality_enum CASCADE;

DROP SCHEMA IF EXISTS ytxz CASCADE;
CREATE SCHEMA IF NOT EXISTS ytxz;

----------------------------------- user_type_enum table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.user_type_enum (
   enum_id serial,
   role VARCHAR(20) NOT NULL,
   PRIMARY KEY(role),
   UNIQUE(enum_id)
);

INSERT INTO ytxz.user_type_enum (role) VALUES ('admin');
INSERT INTO ytxz.user_type_enum (role) VALUES ('normal');
INSERT INTO ytxz.user_type_enum (role) VALUES ('guest');

----------------------------------- file_type_enum table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.file_type_enum (
   enum_id serial,
   file_type VARCHAR(20) NOT NULL,
   PRIMARY KEY(file_type),
   UNIQUE(enum_id)
);

INSERT INTO ytxz.file_type_enum (file_type) VALUES ('video');
INSERT INTO ytxz.file_type_enum (file_type) VALUES ('audio');

----------------------------------- quality_enum table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.quality_enum (
   enum_id serial,
   quality VARCHAR(20) NOT NULL,
   PRIMARY KEY(quality),
   UNIQUE(enum_id)
);

INSERT INTO ytxz.quality_enum (quality) VALUES ('default');
INSERT INTO ytxz.quality_enum (quality) VALUES ('mp3');
INSERT INTO ytxz.quality_enum (quality) VALUES ('mp4');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_144');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_240');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_360');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_480');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_720');
INSERT INTO ytxz.quality_enum (quality) VALUES ('height_lt_1080');

----------------------------------- users table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.users (
   uid serial,
   fname VARCHAR(50) NOT NULL,
   lname VARCHAR(50) NOT NULL,
   user_name VARCHAR(50) NOT NULL,
   passwd VARCHAR(300) NOT NULL,
   role_id INT NOT NULL,
   PRIMARY KEY(user_name),
   UNIQUE(uid),
   FOREIGN KEY(role_id) REFERENCES ytxz.user_type_enum(enum_id)
);

INSERT INTO ytxz.users (fname, lname, user_name, passwd, role_id) VALUES ('Mason',   'You',   'myou',  '61b68f9a1225f6336d86621622355c961c131f70', (SELECT enum_id FROM ytxz.user_type_enum WHERE role = 'admin'));
INSERT INTO ytxz.users (fname, lname, user_name, passwd, role_id) VALUES ('Unknown', 'Guest', 'guest', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e', (SELECT enum_id FROM ytxz.user_type_enum WHERE role = 'guest'));

----------------------------------- history table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.history (
   hid serial,
   uid INT NOT NULL,
   name VARCHAR(500),         -- file title (override youtube title if not NULL)
   url VARCHAR(100),          -- youtube URL (could be a playlist)
   folder_name VARCHAR(100),  -- if stored in a separate folder
   dl_time TIMESTAMPTZ DEFAULT NOW(),
   pl_start INT DEFAULT 1,    -- play list start (if applicable)
   pl_stop  INT DEFAULT 100,  -- play list stop  (if applicable)
   file_ext INT NOT NULL,
   quality INT NOT NULL,
   PRIMARY KEY(hid),
   FOREIGN KEY(uid) REFERENCES ytxz.users(uid),
   FOREIGN KEY(file_ext) REFERENCES ytxz.file_type_enum(enum_id),
   FOREIGN KEY(quality) REFERENCES ytxz.quality_enum(enum_id)
);

----------------------------------- favorite table ------------------------------
CREATE TABLE IF NOT EXISTS ytxz.favorite (
   fid serial,
   uid INT NOT NULL,
   name VARCHAR(500),         -- file title (override youtube title if not NULL)
   url VARCHAR(100),          -- youtube URL (could be a playlist)
   folder_name VARCHAR(100),  -- if stored in a separate folder
   time_added TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(fid),
   FOREIGN KEY(uid) REFERENCES ytxz.users(uid)
);

INSERT INTO ytxz.favorite (uid, name, url, folder_name) VALUES ( (SELECT uid FROM ytxz.users WHERE user_name = 'myou'), 'kids car music', 'https://youtube.com/playlist?list=PLKkN4aWr9O7E3gSppfUDYXmjziweNhT8C', 'kids car music');
INSERT INTO ytxz.favorite (uid, name, url, folder_name) VALUES ( (SELECT uid FROM ytxz.users WHERE user_name = 'myou'), '新闻', 'https://youtube.com/playlist?list=PLKkN4aWr9O7ET0t1cemw0ZoOGGnSikzdr', '新闻');
