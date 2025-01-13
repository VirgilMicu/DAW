


CREATE TABLE utilizatori (    
    id_user INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT, 
    tip TINYINT UNSIGNED  NOT NULL DEFAULT 1, 
    nume VARCHAR(50)  NOT NULL , 
    prenume VARCHAR(50)  NOT NULL , 
    email VARCHAR(100) UNIQUE NOT NULL , 
    parola VARCHAR(100)  NOT NULL , 
    data_inregistrare DATETIME  NOT NULL 
) ENGINE=INNODB;
	
CREATE TABLE productii (	
	id_productie INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT, 
	titlu VARCHAR(100)  NOT NULL , 
	descriere_scurta VARCHAR(200)  NOT NULL , 
	descriere_interna VARCHAR(50)  NOT NULL , 
	descriere VARCHAR(1000)  NOT NULL , 
	status TINYINT UNSIGNED  NOT NULL DEFAULT 1
) ENGINE=INNODB;	
	
CREATE TABLE spectacole (	
	id_spectacol INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT, 
	id_productie INT UNSIGNED  NOT NULL , 
	data_spectacol DATE  NOT NULL , 
	status TINYINT UNSIGNED  NOT NULL DEFAULT 1, 
	CONSTRAINT FK_productii_spectacole FOREIGN KEY (id_productie) REFERENCES productii(id_productie),
	CONSTRAINT UK_spectacole_data_spectacol UNIQUE KEY (data_spectacol)
) ENGINE=INNODB;	
	
	
CREATE TABLE rezervari (	
	id_rezervare INT UNSIGNED PRIMARY KEY NOT NULL, 
	id_user INT UNSIGNED  NOT NULL , 
	id_spectacol INT UNSIGNED  NOT NULL,
	data_adaugare TIMESTAMP NOT NULL DEFAULT NOW(),
	cod_control INT UNSIGNED  NOT NULL , 	
	CONSTRAINT FK_utilizatori_rezervari FOREIGN KEY (id_user) REFERENCES utilizatori(id_user),
	CONSTRAINT FK_spectacole_rezervari FOREIGN KEY (id_spectacol) REFERENCES spectacole(id_spectacol)
) ENGINE=INNODB;	
	
	
CREATE TABLE locuri_sala (	
	id_loc TINYINT UNSIGNED PRIMARY KEY NOT NULL
) ENGINE=INNODB;
	
	
CREATE TABLE rezervari_locuri (	
	id_rezervare INT UNSIGNED  NOT NULL , 
	id_loc TINYINT UNSIGNED  NOT NULL , 
	id_spectacol INT UNSIGNED  NOT NULL,
	CONSTRAINT  PRIMARY KEY (id_rezervare, id_loc),
	CONSTRAINT FK_rezervari_rezervari_locuri FOREIGN KEY (id_rezervare) REFERENCES rezervari(id_rezervare) ON DELETE CASCADE,
	CONSTRAINT FK_spectacole_rezervari_locuri FOREIGN KEY (id_spectacol) REFERENCES spectacole(id_spectacol),
	CONSTRAINT CK_id_loc CHECK (id_loc BETWEEN 1 AND 100),
	CONSTRAINT UK_loc_spectacol UNIQUE KEY (id_loc, id_spectacol)
) ENGINE=INNODB;	
	
	
CREATE TABLE accesari (	
	pagina VARCHAR(50)  NOT NULL , 
	id_user INT UNSIGNED, 
	ip VARBINARY(16)  NOT NULL , 
	tsp TIMESTAMP NOT NULL  
) ENGINE=MyISAM;
	
	
CREATE TABLE logari (	
	id_user INT UNSIGNED  NOT NULL AUTO_INCREMENT, 
	tsp TIMESTAMP  NOT NULL , 
	ip VARBINARY(16)  NOT NULL , 
	CONSTRAINT FK_utilizatori_logari FOREIGN KEY (id_user) REFERENCES utilizatori(id_user)
) ENGINE=MyISAM;	

	
	CREATE OR REPLACE VIEW productii_spectacole AS
	SELECT p.*, IFNULL(nr_s,0) AS nr_spectacole 
		FROM productii p LEFT JOIN (select id_productie, count(*) AS nr_s FROM spectacole GROUP BY id_productie) AS s 
		ON p.id_productie = s.id_productie 
		ORDER BY id_productie DESC;
		
		
	CREATE OR REPLACE VIEW spectacole_locuri AS
	SELECT s.*, p.titlu, p.descriere_interna, p.descriere_scurta, p.descriere, IFNULL(nr_loc,0) AS nr_locuri_vandute 
	FROM spectacole s 
	JOIN productii p ON s.id_productie = p.id_productie 
	LEFT JOIN (SELECT r.id_spectacol, count(*) AS nr_loc FROM rezervari r LEFT JOIN rezervari_locuri rl ON r.id_rezervare = rl.id_rezervare GROUP BY r.id_spectacol) AS nr_rez ON s.id_spectacol = nr_rez.id_spectacol
			ORDER BY data_spectacol ASC;

	CREATE OR REPLACE VIEW spectacole_rezervari AS
	SELECT s.*, IFNULL(r.id_rezervare, 0) id_rezervare, IFNULL(r.id_user, 0) id_user, IFNULL(id_loc, 0) id_loc
	FROM spectacole s LEFT JOIN rezervari r ON s.id_spectacol = r.id_spectacol 
		LEFT JOIN rezervari_locuri rl on r.id_rezervare = rl.id_rezervare ORDER BY s.id_spectacol DESC, id_loc ASC;
		
	CREATE OR REPLACE VIEW rezervari_lista_locuri AS
	SELECT s.id_spectacol, p.titlu, s.data_spectacol, r.id_user, r.id_rezervare, r.data_adaugare, r.cod_control, GROUP_CONCAT(rl.id_loc ORDER BY rl.id_loc ASC) AS lista_locuri
	FROM rezervari r 
	JOIN spectacole s ON r.id_spectacol = s.id_spectacol
	JOIN productii p ON s.id_productie = p.id_productie
	JOIN rezervari_locuri rl ON r.id_rezervare = rl.id_rezervare
		GROUP BY r.id_rezervare
		ORDER BY data_spectacol DESC, data_adaugare DESC;
		
	CREATE OR REPLACE VIEW statistici_pag_ip_zile AS
	SELECT date_format(tsp, '%Y-%m-%d') AS Data, pagina AS Pagina, INET6_NTOA(ip) AS IP, count(*) AS 'Nr. accesari' FROM accesari
	GROUP BY Pagina, Data, IP
	ORDER BY Data DESC, count(*) DESC;

	CREATE OR REPLACE VIEW statistici_pag_zile AS
	SELECT date_format(tsp, '%Y-%m-%d') AS Data, pagina AS Pagina, COUNT(*) AS 'Nr. accesari' FROM accesari
	GROUP BY Pagina, Data
	ORDER BY Data DESC, count(*) DESC;
	
	
	CREATE OR REPLACE VIEW statistici_logari_useri AS
	SELECT u.id_user AS 'ID utilizator', email AS Email, CONCAT(u.nume,' ', u.prenume) as Nume, INET6_NTOA(ip) as IP, tsp 'Timestamp' 
    FROM utilizatori u JOIN logari l ON u.id_user = l.id_user
	ORDER BY tsp DESC;

	CREATE OR REPLACE VIEW vizitatori_unici_zi AS
	SELECT Data_zi AS Data, COUNT(IP) AS 'Vizitatori unici' 
	FROM (SELECT DISTINCT date_format(tsp, '%Y-%m-%d') AS Data_zi, INET6_NTOA(ip) as IP
			FROM accesari) AS vu 
	GROUP BY Data
	ORDER BY Data DESC;