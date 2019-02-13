-- admin:test

INSERT INTO `test_users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`, `lastResetTime`, `resetCount`)
VALUES
	(1234, 'Test Super User', 'admin', 'test@example.com', '37bfa05839144bde2eeaf8b458ba9724:DUPyKkxHOtMSXKifszlEmedO6FFXXd6p',  0, 1, '2014-10-01 09:00:00', '2014-10-01 09:00:00', '', '{\"admin_style\":\"\",\"admin_language\":\"\",\"language\":\"\",\"editor\":\"\",\"helpsite\":\"\",\"timezone\":\"UTC\"}', '0000-00-00 00:00:00', 0);

INSERT INTO `test_user_usergroup_map` (`user_id`, `group_id`)
VALUES
	(1234, 8);
