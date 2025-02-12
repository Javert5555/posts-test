CREATE TABLE posts (
	id serial PRIMARY KEY,
	user_id integer,
	title varchar(255),
	body text
);

CREATE TABLE comments (
	id serial PRIMARY KEY,
	post_id integer REFERENCES posts(id),
	name TEXT,
	email varchar(255),
	body text
);