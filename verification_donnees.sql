-- Requêtes SQL pour vérifier les données dans les tables API

-- Vérifier le nombre de courses dans les différentes tables
SELECT 
    'courses (ancienne table admin)' as table_name,
    COUNT(*) as count,
    MAX(created_at) as last_created
FROM courses
UNION ALL
SELECT 
    'api_courses (table mobile)' as table_name,
    COUNT(*) as count,
    MAX(created_at) as last_created
FROM api_courses;

-- Vérifier les utilisateurs par rôle
SELECT 
    role,
    COUNT(*) as count,
    MAX(created_at) as last_created
FROM users 
GROUP BY role;

-- Vérifier les chauffeurs dans les différentes tables
SELECT 
    'chauffeurs (ancienne table)' as table_name,
    COUNT(*) as count
FROM chauffeurs
UNION ALL
SELECT 
    'users role=chauffeur (table API)' as table_name,
    COUNT(*) as count
FROM users 
WHERE role = 'chauffeur';

-- Vérifier les clients dans les différentes tables
SELECT 
    'clients (ancienne table)' as table_name,
    COUNT(*) as count
FROM clients
UNION ALL
SELECT 
    'users role=client (table API)' as table_name,
    COUNT(*) as count
FROM users 
WHERE role = 'client';

-- Statistiques des courses API par statut
SELECT 
    statut,
    COUNT(*) as count,
    SUM(prix_final) as total_revenue,
    AVG(prix_final) as avg_price
FROM api_courses 
GROUP BY statut;

-- Courses créées aujourd'hui
SELECT 
    COUNT(*) as courses_today,
    SUM(CASE WHEN statut = 'terminee' THEN prix_final ELSE 0 END) as revenue_today
FROM api_courses 
WHERE DATE(created_at) = CURDATE();

-- Vérifier les notifications
SELECT 
    'notifications (ancienne table)' as table_name,
    COUNT(*) as count
FROM notifications
UNION ALL
SELECT 
    'api_notifications (table mobile)' as table_name,
    COUNT(*) as count
FROM api_notifications;
