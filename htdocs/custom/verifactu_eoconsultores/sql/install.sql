-- SQL schema for VeriFactu EO Consultores

CREATE TABLE IF NOT EXISTS llx_verifactu_register (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    entity INTEGER NOT NULL DEFAULT 1,
    type VARCHAR(16) NOT NULL,
    fk_facture INTEGER NOT NULL,
    ref VARCHAR(128) NOT NULL,
    tms INTEGER NOT NULL,
    emisor_nif VARCHAR(32) NOT NULL,
    importe_total DECIMAL(24,8) NOT NULL DEFAULT 0,
    mode VARCHAR(20) NOT NULL,
    system_id VARCHAR(64),
    fk_user INTEGER NOT NULL,
    datec TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=innodb;

CREATE TABLE IF NOT EXISTS llx_verifactu_hash (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    fk_register INTEGER NOT NULL,
    previous_hash VARCHAR(128),
    hash_value VARCHAR(128) NOT NULL,
    signature TEXT,
    datec TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_verifactu_hash_fk_register (fk_register)
) ENGINE=innodb;

CREATE TABLE IF NOT EXISTS llx_verifactu_log (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    entity INTEGER NOT NULL DEFAULT 1,
    user_login VARCHAR(64) NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    datec TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=innodb;
