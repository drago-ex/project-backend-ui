--
--  Privileges of the administration dashboard for drago-ex/project-permission
-- ---------------------------------------------------------------------------
-- Imported by hand into the tables of project-permission (see the README), so a role can be given the dashboard:
-- it is the entry to the administration and its menu, which then shows only the sections of the role.
INSERT IGNORE INTO resources (resource, privilege, description) VALUES
    ('Backend:Admin', 'default-read', 'View the dashboard');
