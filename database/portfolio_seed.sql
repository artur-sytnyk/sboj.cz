PRAGMA foreign_keys = ON;

BEGIN TRANSACTION;

-- All portfolio demo accounts use the password: Demo123
INSERT OR IGNORE INTO user (
    user_type, active, username, password, email, fullname,
    birth_date, specialization, description, file_path
) VALUES
    (2, 1, 'northstar-labs',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'careers@northstar.example.test', 'Northstar Labs', NULL,
     'Cloud platforms and developer tools',
     'A Prague-based product company building dependable tools for modern engineering teams.', ''),
    (2, 1, 'greenline-studio',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'jobs@greenline.example.test', 'Greenline Studio', NULL,
     'Digital products and brand experiences',
     'A multidisciplinary Brno studio creating accessible digital products and memorable brands.', ''),
    (2, 1, 'morava-digital',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'team@moravadigital.example.test', 'Morava Digital', NULL,
     'Business software and mobile applications',
     'An Olomouc software house delivering secure web and mobile solutions for growing businesses.', ''),
    (1, 1, 'john-doe',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'john.doe@example.test', 'John Doe', '1997-04-12',
     'PHP Developer',
     'Backend developer focused on clean PHP applications, APIs, and relational databases.',
     ''),
    (1, 1, 'emily-white',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'emily.white@example.test', 'Emily White', '1998-09-23',
     'UX/UI Designer',
     'Product designer interested in accessible interfaces, research, and thoughtful design systems.',
     ''),
    (1, 1, 'daniel-smith',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'daniel.smith@example.test', 'Daniel Smith', '1995-01-17',
     'DevOps Engineer',
     'Infrastructure engineer experienced with CI/CD, containers, monitoring, and cloud services.',
     ''),
    (1, 1, 'laura-jones',
     '$5$rounds=5000$trebamozna$AGxzo1eB/HiFTxFTvKz8yFa51jleTHgqGBkUvTVPXg/',
     'laura.jones@example.test', 'Laura Jones', '1996-07-05',
     'Data Analyst',
     'Analyst who turns product and customer data into clear insights and practical recommendations.',
     '');

WITH seed_jobs (
    owner_username, company_name, title, address, description,
    benefits, logo_path, homepage_link
) AS (
    VALUES
        ('northstar-labs', 'Northstar Labs', 'Backend PHP Engineer', 'Prague',
         'Design APIs and backend services for a growing cloud platform using modern PHP and SQL.',
         'Hybrid work, education budget, five weeks holiday', '', 'https://www.example.com/jobs/backend-php'),
        ('northstar-labs', 'Northstar Labs', 'QA Automation Engineer', 'Prague',
         'Build automated test suites and help teams ship reliable web applications with confidence.',
         'Flexible hours, modern equipment, conference budget', '', 'https://www.example.com/jobs/qa-automation'),
        ('northstar-labs', 'Northstar Labs', 'DevOps Engineer', 'Remote / Prague',
         'Improve CI/CD pipelines, observability, and cloud infrastructure for our product teams.',
         'Remote work, home-office budget, on-call compensation', '', 'https://www.example.com/jobs/devops'),
        ('northstar-labs', 'Northstar Labs', 'Data Analyst', 'Prague',
         'Explore product data, create dashboards, and help teams make evidence-based decisions.',
         'Hybrid work, mentoring, learning allowance', '', 'https://www.example.com/jobs/data-analyst'),
        ('northstar-labs', 'Northstar Labs', 'Product Designer', 'Prague',
         'Shape intuitive workflows for technical users from discovery through polished delivery.',
         'Flexible hours, design events, premium hardware', '', 'https://www.example.com/jobs/product-designer'),
        ('greenline-studio', 'Greenline Studio', 'UX/UI Designer', 'Brno',
         'Create accessible interfaces and design systems for web and mobile client projects.',
         'Creative studio, flexible schedule, training budget', '', 'https://www.example.com/jobs/ux-ui'),
        ('greenline-studio', 'Greenline Studio', 'React Developer', 'Brno / Hybrid',
         'Develop fast, responsive frontends and collaborate closely with designers and backend teams.',
         'Hybrid work, latest laptop, team retreats', '', 'https://www.example.com/jobs/react'),
        ('greenline-studio', 'Greenline Studio', 'Content Strategist', 'Brno',
         'Plan clear product content and campaigns that connect digital brands with their audiences.',
         'Flexible hours, wellness allowance, creative workshops', '', 'https://www.example.com/jobs/content-strategist'),
        ('greenline-studio', 'Greenline Studio', 'Project Coordinator', 'Brno',
         'Coordinate timelines, client communication, and delivery across a multidisciplinary studio.',
         'Mentoring, language courses, flexible schedule', '', 'https://www.example.com/jobs/project-coordinator'),
        ('morava-digital', 'Morava Digital', 'Java Developer', 'Olomouc',
         'Build maintainable business applications and integrations for customers across Europe.',
         'Hybrid work, meal allowance, certification budget', '', 'https://www.example.com/jobs/java'),
        ('morava-digital', 'Morava Digital', 'Mobile App Developer', 'Olomouc / Remote',
         'Deliver polished mobile experiences and contribute to shared application architecture.',
         'Remote work, flexible hours, new equipment', '', 'https://www.example.com/jobs/mobile'),
        ('morava-digital', 'Morava Digital', 'Cybersecurity Specialist', 'Olomouc',
         'Review application security, support audits, and strengthen secure development practices.',
         'Certification budget, extra holiday, flexible hours', '', 'https://www.example.com/jobs/security'),
        ('morava-digital', 'Morava Digital', 'Technical Support Specialist', 'Olomouc',
         'Help customers resolve technical issues and turn recurring questions into better documentation.',
         'Training plan, flexible shifts, performance bonus', '', 'https://www.example.com/jobs/support')
)
INSERT INTO job (
    id_owner, company_name, title, address, description,
    benefits, logo_path, homepage_link
)
SELECT
    u.id, sj.company_name, sj.title, sj.address, sj.description,
    sj.benefits, sj.logo_path, sj.homepage_link
FROM seed_jobs sj
JOIN user u ON u.username = sj.owner_username
WHERE NOT EXISTS (
    SELECT 1
    FROM job j
    WHERE j.company_name = sj.company_name AND j.title = sj.title
);

-- The Junior PHP Developer opening has every applicant. All other seeded
-- openings have smaller applicant groups, providing varied employer views.
WITH seed_applications (applicant_username, company_name, job_title) AS (
    VALUES
        ('john-doe', 'Demo Company', 'Junior PHP Developer'),
        ('emily-white', 'Demo Company', 'Junior PHP Developer'),
        ('daniel-smith', 'Demo Company', 'Junior PHP Developer'),
        ('laura-jones', 'Demo Company', 'Junior PHP Developer'),
        ('john-doe', 'Demo Company', 'Frontend Developer'),
        ('emily-white', 'Demo Company', 'Frontend Developer'),
        ('laura-jones', 'Demo Company', 'Frontend Developer'),
        ('john-doe', 'Northstar Labs', 'Backend PHP Engineer'),
        ('daniel-smith', 'Northstar Labs', 'Backend PHP Engineer'),
        ('emily-white', 'Northstar Labs', 'QA Automation Engineer'),
        ('daniel-smith', 'Northstar Labs', 'QA Automation Engineer'),
        ('daniel-smith', 'Northstar Labs', 'DevOps Engineer'),
        ('laura-jones', 'Northstar Labs', 'Data Analyst'),
        ('emily-white', 'Northstar Labs', 'Product Designer'),
        ('laura-jones', 'Northstar Labs', 'Product Designer'),
        ('john-doe', 'Greenline Studio', 'UX/UI Designer'),
        ('emily-white', 'Greenline Studio', 'UX/UI Designer'),
        ('laura-jones', 'Greenline Studio', 'UX/UI Designer'),
        ('john-doe', 'Greenline Studio', 'React Developer'),
        ('daniel-smith', 'Greenline Studio', 'React Developer'),
        ('emily-white', 'Greenline Studio', 'Content Strategist'),
        ('emily-white', 'Greenline Studio', 'Project Coordinator'),
        ('laura-jones', 'Greenline Studio', 'Project Coordinator'),
        ('john-doe', 'Morava Digital', 'Java Developer'),
        ('emily-white', 'Morava Digital', 'Mobile App Developer'),
        ('daniel-smith', 'Morava Digital', 'Mobile App Developer'),
        ('daniel-smith', 'Morava Digital', 'Cybersecurity Specialist'),
        ('laura-jones', 'Morava Digital', 'Technical Support Specialist')
)
INSERT OR IGNORE INTO rel_user_job (id_user, id_job)
SELECT u.id, j.id
FROM seed_applications sa
JOIN user u ON u.username = sa.applicant_username
JOIN job j ON j.company_name = sa.company_name AND j.title = sa.job_title;

COMMIT;
