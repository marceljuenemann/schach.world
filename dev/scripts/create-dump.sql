/**
 * Anonymize sensitive data in the database.
 * This script updates various tables to replace personal information
 * with generic or randomized data while preserving the structure of the data.
 */

UPDATE `bemerkungen` SET text = "This is an example match day comment."

UPDATE `benutzer` SET 
  name = CONCAT("User ", SUBSTRING(MD5(RAND()), 1, 5)),
  passwort = MD5("123123"),
  letzterzugriff = NOW(),
  random = 0,
  telefon = IF(telefon IS NULL, NULL, IF(telefon = "", "", "0190 123456")),
  telefon2 = IF(telefon2 IS NULL, NULL, IF(telefon2 = "", "", "0900 123456")),
  email = CONCAT("benutzer-", SUBSTRING(MD5(RAND()), 1, 5), "@schach.world");

UPDATE `mannschaften` SET
  so_strasse = IF(so_strasse = "", "", "Musterstraße 1"),
  so_telefon = IF(so_telefon IS NULL, NULL, IF(so_telefon = "", "", "0190 654321")),
  mf_name = CONCAT("Manager ", SUBSTRING(MD5(RAND()), 1, 5)),
  mf_email = CONCAT("manager-", SUBSTRING(MD5(RAND()), 1, 5), "@schach.world"),
  mf_telefon = IF(mf_telefon IS NULL, NULL, IF(mf_telefon = "", "", "0900 654321")),
  mf_telefon2 = IF(mf_telefon2 IS NULL, NULL, IF(mf_telefon2 = "", "", "0180 654321"));

UPDATE paarungen SET
  bemerkung = IF(bemerkung IS NULL, NULL, IF(bemerkung = "", "", "This is an example remark for the match."));

UPDATE spieler SET
  zps = IF(LENGTH(zps)>6, CONCAT(SUBSTRING(zps, 1, 6), FLOOR(RAND() * 1500 + 100)), zps),
  vorname = SUBSTRING(MD5(RAND()), 1, 7),
  nachname = CONCAT('Player ', SUBSTRING(MD5(RAND()), 1, 4)),
  geburt = IF(geburt = '', '', FLOOR(RAND() * 60 + 1950));
