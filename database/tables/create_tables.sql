--
--DROP TABLES
--	

DROP TABLE accounts CASCADE;
DROP TABLE messages CASCADE;
DROP TABLE patient_account CASCADE;
DROP TABLE hcp_account CASCADE;
DROP TABLE appointments CASCADE;
DROP TABLE medical_record CASCADE;
DROP TABLE payment CASCADE;
--DROP TABLE p_d_Connection CASCADE;
--DROP TABLE d_d_Connection CASCADE;
DROP TABLE connections CASCADE;
DROP TABLE permission CASCADE;
DROP TABLE ci_sessions CASCADE;
DROP TABLE groups CASCADE;
DROP TABLE refers;
DROP TABLE is_in;
DROP TABLE invite;


--
--CREATE TABLES
--

--accounts Table
CREATE TABLE accounts(
	account_id SERIAL NOT NULL,
	email VARCHAR(40) NOT NULL,
	password VARCHAR(15) NOT NULL,
	active BOOLEAN DEFAULT TRUE,
	private BOOLEAN DEFAULT FALSE NOT NULL,
	UNIQUE(email),
	PRIMARY KEY(account_id)
);


--Messages Table
CREATE TABLE messages(
	message_id SERIAL NOT NULL,
	sender_id SERIAL NOT NULL,
	receiver_id SERIAL NOT NULL,
	sender_kept BOOLEAN NOT NULL DEFAULT TRUE,
	receiver_kept BOOLEAN NOT NULL DEFAULT TRUE, 
	subject TEXT NOT NULL,
	content TEXT NOT NULL,
	datetime TIMESTAMP(0) WITH TIME ZONE NOT NULL,
	PRIMARY KEY(message_id),
	FOREIGN KEY (sender_id) REFERENCES accounts(account_id),
	FOREIGN KEY (receiver_id) REFERENCES accounts(account_id)
);


--Patient Table
CREATE TABLE patient_account(
	account_id SERIAL NOT NULL,
	--patient_id SERIAL NOT NULL,
	first_name VARCHAR(30) NOT NULL,
	last_name VARCHAR(30) NOT NULL,
	middle_name VARCHAR(30),
	ssn NUMERIC(9,0) NOT NULL,
	dob DATE NOT NULL,
	sex CHAR(1) NOT NULL CHECK ((sex='f') OR (sex='m')),
	tel_number VARCHAR(11),
	fax_number VARCHAR(11),
	address TEXT,
	picture_name TEXT,
	PRIMARY KEY(account_id),
	FOREIGN KEY (account_id) REFERENCES accounts(account_id)
);



--hcp_account Table
CREATE TABLE hcp_account(
	account_id SERIAL NOT NULL,
	--hcp_id SERIAL NOT NULL,
	first_name VARCHAR(30) NOT NULL,
	last_name VARCHAR(30) NOT NULL,
	middle_name VARCHAR(30),
	ssn NUMERIC(9,0) NOT NULL,
	dob DATE NOT NULL,
	sex CHAR(1) NOT NULL CHECK ((sex='f') OR (sex='m')),
	tel_number VARCHAR(11),
	fax_number VARCHAR(11),
	specialization text,
	org_name VARCHAR(30),
	address TEXT,
	picture_name TEXT,
	PRIMARY KEY(account_id),
	FOREIGN KEY (account_id) REFERENCES accounts(account_id)
);


--Apointments Table
CREATE TABLE appointments(
	appointment_id SERIAL NOT NULL,
	patient_id SERIAL NOT NULL,
	hcp_id SERIAL NOT NULL,
	descryption TEXT NOT NULL,
	date_time TIMESTAMP(0) WITH TIME ZONE NOT NULL,
	approved BOOLEAN NOT NULL DEFAULT FALSE,
	PRIMARY KEY(appointment_id),
	FOREIGN KEY (patient_id) REFERENCES patient_account(account_id),
	FOREIGN KEY (hcp_id) REFERENCES hcp_account(account_id)
);


--Medical_Records Table
CREATE TABLE medical_record(
	medical_rec_id SERIAL NOT NULL,
	patient_id SERIAL NOT NULL,
	account_id SERIAL NOT NULL, 
	issue TEXT NOT NULL,
	suplementary_info TEXT,
	file_name TEXT NOT NULL,
	description TEXT NOT NULL,
	date_created TIMESTAMP DEFAULT now(),
	PRIMARY KEY (medical_rec_id),
	FOREIGN KEY (patient_id) REFERENCES patient_account(account_id),
	FOREIGN KEY (account_id) REFERENCES accounts(account_id)	
);


--Payment Table
CREATE TABLE payment(
	bill_id SERIAL NOT NULL,
	patient_id SERIAL NOT NULL,
	hcp_id SERIAL NOT NULL,
	amount DECIMAL(9,2) NOT NULL,
	descryption TEXT NOT NULL,
	due_date TIMESTAMP(0) WITH TIME ZONE NOT NULL,
	cleared BOOLEAN NOT NULL DEFAULT FALSE,
	hcp_kept BOOLEAN NOT NULL DEFAULT TRUE,
	patient_kept BOOLEAN NOT NULL DEFAULT TRUE, 
	creation_date TIMESTAMP DEFAULT now(),
	PRIMARY KEY(bill_id),
	FOREIGN KEY (patient_id) REFERENCES patient_account(account_id),
	FOREIGN KEY (hcp_id) REFERENCES hcp_account(account_id)

);


--Permissions Medical Recorda Table
CREATE TABLE permission(
	permission_id SERIAL NOT NULL,
	medical_rec_id SERIAL NOT NULL,
	account_id SERIAL NOT NULL,
	date_created DATE NOT NULL,
	type VARCHAR(1) DEFAULT '0', -- 0 is read access 1 is write access
	PRIMARY KEY (permission_id),
	UNIQUE (medical_rec_id,account_id),
	FOREIGN KEY (medical_rec_id) REFERENCES medical_record(medical_rec_id) ON DELETE CASCADE,
	FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE

);


--Refers Table
CREATE TABLE refers(
	referal_id SERIAL NOT NULL,
	refering_id SERIAL NOT NULL,
	is_refered_id SERIAL NOT NULL,
	patient_id SERIAL NOT NULL,
	date_time TIMESTAMP DEFAULT now(),
	status BOOLEAN DEFAULT FALSE,
	PRIMARY KEY(referal_id),
	FOREIGN KEY (refering_id) REFERENCES hcp_account(account_id),
	FOREIGN KEY (is_refered_id) REFERENCES hcp_account(account_id),
	FOREIGN KEY (patient_id) REFERENCES patient_account(account_id)
);


--Group Table
CREATE TABLE groups(
	group_id SERIAL NOT NULL,
	account_id SERIAL NOT NULL,
	name TEXT NOT NULL,
	description TEXT NOT NULL,
	public_private VARCHAR(1) NOT NULL,
	group_type VARCHAR(1) NOT NULL,
	PRIMARY KEY(group_id),
	FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
);

--Connections Table
CREATE TABLE connections(
	connection_id SERIAL NOT NULL,
	--requester_id SERIAL NOT NULL,
	--accepter_id SERIAL NOT NULL,
	sender_id SERIAL NOT NULL,
	receiver_id SERIAL NOT NULL,
	accepted BOOLEAN NOT NULL DEFAULT FALSE,
	date_connected DATE NOT NULL,
	sender_level VARCHAR(1) DEFAULT '0',
	receiver_level VARCHAR(1) DEFAULT '0',
	PRIMARY KEY (connection_id),
	UNIQUE(sender_id,receiver_id),
	FOREIGN KEY (sender_id) REFERENCES accounts(account_id),
	FOREIGN KEY (receiver_id) REFERENCES accounts(account_id)
);
	
CREATE TABLE invite(
	inviter_id SERIAL NOT NULL,
	invitee_id SERIAL NOT NULL,
	group_id SERIAL NOT NULL,
	PRIMARY KEY(invitee_id,group_id),
	FOREIGN KEY (inviter_id) REFERENCES accounts(account_id) ON DELETE CASCADE, 
	FOREIGN KEY (invitee_id) REFERENCES accounts(account_id) ON DELETE CASCADE,
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE
);

--
--RELATIONSHIP TABLES
--
/*
--Patient to Doctor Table
CREATE TABLE p_d_connection(
	patient_id SERIAL NOT NULL,
	hcp_id SERIAL NOT NULL,
	accepted BOOLEAN NOT NULL DEFAULT FALSE,
	date_connected DATE NOT NULL,
	PRIMARY KEY(patient_id, hcp_id),
	FOREIGN KEY (patient_id) REFERENCES patient_account(account_id),
	FOREIGN KEY (hcp_id) REFERENCES hcp_account(account_id)
);


--Doctor to Doctor Table
CREATE TABLE d_d_connection(
	requester_id serial NOT NULL,
	accepter_id serial NOT NULL,
	accepted BOOLEAN NOT NULL DEFAULT FALSE,
	date_connected DATE NOT NULL,
	PRIMARY KEY (requester_id, accepter_id),
	FOREIGN KEY (requester_id) REFERENCES hcp_account(account_id),
	FOREIGN KEY (accepter_id) REFERENCES hcp_account(account_id)
);

*/
--Is In Table
CREATE TABLE is_in(
	account_id SERIAL NOT NULL,
	group_id SERIAL NOT NULL,
	permissions VARCHAR(1) DEFAULT '0' NOT NULL,
	PRIMARY KEY (account_id, group_id),
	FOREIGN KEY (account_id) REFERENCES accounts(account_id),
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE
);


CREATE TABLE ci_sessions (
	session_id varchar(40) DEFAULT '0' NOT NULL,
	ip_address varchar(16) DEFAULT '0' NOT NULL,
	user_agent varchar(50) NOT NULL,
	last_activity integer DEFAULT 0 NOT NULL,
	user_data text NOT NULL,
	PRIMARY KEY (session_id)
);

