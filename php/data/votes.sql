CREATE TABLE votes_info(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vote_title VARCHAR(255),
        left_text TEXT,
        right_text TEXT
);
CREATE TABLE votes(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        side BOOLEAN,
        votes_info_id INTEGER,
        FOREIGN KEY(votes_info_id) REFERENCES votes_info(id)
);
CREATE TABLE options(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key TEXT,
        value BLOB
);
CREATE TABLE admins(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	username TEXT,
	password VARCHAR(60),
	UNIQUE(username) ON CONFLICT REPLACE
);