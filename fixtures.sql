BEGIN;

-- =================================================================
-- RESET COMPLET (ordre FK)
-- =================================================================
TRUNCATE TABLE
    verifications_conflit,
    actes_generes,
    modeles_actes,
    sessions_temps,
    ecritures_comptables,
    paiements,
    lignes_facture,
    factures,
    documents_dossier,
    chronologie,
    echeances,
    rendezvous,
    parties_adverses,
    dossiers,
    clients,
    cabinet_config
RESTART IDENTITY CASCADE;

-- =================================================================
-- CABINET CONFIG
-- =================================================================
INSERT INTO cabinet_config (id, nom, adresse, code_postal, ville, telephone, email, siret, avocat_nom, avocat_barreau, avocat_numero, couleurs_matiere, taux_horaire_defaut, updated_at) VALUES (
    1,
    'Cabinet Maître Dupuis & Associés',
    '15 rue de la Paix',
    '75002',
    'Paris',
    '01 42 68 47 23',
    'contact@cabinet-dupuis.fr',
    '452 981 673 00021',
    'Sophie DUPUIS',
    'Paris',
    'P-2847',
    '{"Droit familial":"#EC4899","Droit pénal":"#EF4444","Droit des affaires":"#1E40AF","Droit social":"#F97316","Droit immobilier":"#059669","Droit administratif":"#7C3AED","Droit international":"#06B6D4","Autres":"#6B7280"}',
    250.00,
    NOW()
);

-- =================================================================
-- CLIENTS (30)
-- ID 1-10  : Particuliers M.
-- ID 11-20 : Particuliers Mme.
-- ID 21-25 : Professionnels
-- ID 26-30 : Entreprises
-- ⚠ CONFLITS : client 5 (DUPONT Jean) et client 8 (CHEVALIER Éric)
-- =================================================================
INSERT INTO clients (code_client, type_client, civilite, nom, prenom, email, telephone, adresse, ville, code_postal, intitule, siret, type_societe, created_at, updated_at) VALUES
('CLI-001','Particulier','M.','MARTIN','Antoine','a.martin@email.fr','06 12 34 56 78','8 avenue Victor Hugo','Lyon','69006',NULL,NULL,NULL,NOW(),NOW()),
('CLI-002','Particulier','Mme','BERNARD','Claire','c.bernard@gmail.fr','06 23 45 67 89','22 rue Molière','Marseille','13008',NULL,NULL,NULL,NOW(),NOW()),
('CLI-003','Particulier','M.','MOREAU','François','f.moreau@email.fr','06 34 56 78 90','5 impasse des Lilas','Toulouse','31000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-004','Particulier','Mme','ROUSSEAU','Julie','j.rousseau@email.fr','06 45 67 89 01','3 rue du Château','Bordeaux','33000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-005','Particulier','M.','DUPONT','Jean','j.dupont@email.fr','06 56 78 90 12','14 boulevard Haussmann','Paris','75008',NULL,NULL,NULL,NOW(),NOW()),
('CLI-006','Particulier','M.','PETIT','Antoine','a.petit@email.fr','06 67 89 01 23','7 rue des Acacias','Lille','59000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-007','Particulier','Mme','SIMON','Isabelle','i.simon@email.fr','06 78 90 12 34','19 avenue de la Gare','Nantes','44000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-008','Particulier','M.','CHEVALIER','Éric','e.chevalier@email.fr','06 89 01 23 45','33 rue Nationale','Strasbourg','67000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-009','Particulier','Mme','GARNIER','Nathalie','n.garnier@email.fr','06 90 12 34 56','2 rue Pasteur','Montpellier','34000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-010','Particulier','M.','LEMAIRE','Vincent','v.lemaire@email.fr','07 01 23 45 67','45 avenue Foch','Nice','06000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-011','Particulier','Mme','LEFEBVRE','Marie','m.lefebvre@email.fr','07 12 34 56 78','11 rue de la République','Rennes','35000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-012','Particulier','M.','ROBERT','Pascal','p.robert@email.fr','07 23 45 67 89','6 square Lamartine','Grenoble','38000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-013','Particulier','Mme','THOMAS','Christine','c.thomas@email.fr','07 34 56 78 90','28 rue Vauban','Dijon','21000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-014','Particulier','M.','RICHARD','Olivier','o.richard@email.fr','07 45 67 89 01','9 chemin des Pins','Aix-en-Provence','13100',NULL,NULL,NULL,NOW(),NOW()),
('CLI-015','Particulier','Mme','FONTAINE','Valérie','v.fontaine@email.fr','07 56 78 90 12','17 allée des Roses','Reims','51100',NULL,NULL,NULL,NOW(),NOW()),
('CLI-016','Particulier','M.','MARCHAND','Guillaume','g.marchand@email.fr','07 67 89 01 23','54 route de Lyon','Clermont-Ferrand','63000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-017','Particulier','Mme','LEROUX','Sophie','s.leroux@email.fr','07 78 90 12 34','3 place Carnot','Tours','37000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-018','Particulier','M.','GIRARD','Nicolas','n.girard@email.fr','07 89 01 23 45','21 rue Gambetta','Toulon','83000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-019','Particulier','Mme','BLANC','Anne','a.blanc@email.fr','07 90 12 34 56','8 avenue Jean Jaurès','Amiens','80000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-020','Particulier','M.','DAVID','Sébastien','s.david@email.fr','06 11 22 33 44','15 rue Victor Hugo','Caen','14000',NULL,NULL,NULL,NOW(),NOW()),
('CLI-021','Professionnel','M.','DURAND','Pierre','dr.durand@medecin.fr','01 47 23 58 69','142 boulevard Malesherbes','Paris','75017',NULL,NULL,'Médecin',NOW(),NOW()),
('CLI-022','Professionnel','Mme','PERRIN','Sandrine','s.perrin@expertise.fr','04 72 44 87 32','8 cours Vitton','Lyon','69006',NULL,NULL,'Expert-comptable',NOW(),NOW()),
('CLI-023','Professionnel','M.','MICHAUD','Alain','a.michaud@archi.fr','01 46 22 38 74','55 rue du Faubourg Saint-Honoré','Paris','75008',NULL,NULL,'Architecte',NOW(),NOW()),
('CLI-024','Professionnel','M.','PELLERIN','Christophe','c.pellerin@notariat.fr','02 40 74 51 28','14 rue Crébillon','Nantes','44000',NULL,NULL,'Notaire',NOW(),NOW()),
('CLI-025','Professionnel','Mme','AUBERT','Caroline','c.aubert@finance.fr','05 56 44 73 21','23 cours de l''Intendance','Bordeaux','33000',NULL,NULL,'Conseillère financière',NOW(),NOW()),
('CLI-026','Entreprise',NULL,'','',    'contact@lefebvre-ind.fr','01 44 73 82 91','47 rue de Bercy','Paris','75012','LEFEBVRE INDUSTRIES SAS','84273619100042','SAS',NOW(),NOW()),
('CLI-027','Entreprise',NULL,'','',    'direction@dupont-btp.fr','03 88 35 47 62','89 route de Colmar','Strasbourg','67200','DUPONT BÂTIMENT SARL','53891274600017','SARL',NOW(),NOW()),
('CLI-028','Entreprise',NULL,'','',    'sci@jardinsfleuris.fr','04 93 62 14 87','12 promenade des Anglais','Nice','06000','SCI LES JARDINS FLEURIS','31748259100029','SCI',NOW(),NOW()),
('CLI-029','Entreprise',NULL,'','',    'dg@lambert-finances.fr','01 53 67 84 29','28 place de la Bourse','Paris','75002','LAMBERT FINANCES SA','72638194500033','SA',NOW(),NOW()),
('CLI-030','Entreprise',NULL,'','',    'info@roche-consulting.fr','04 37 65 89 14','3 rue Grenette','Lyon','69001','ROCHE CONSULTING EURL','61459872300025','EURL',NOW(),NOW());

-- =================================================================
-- DOSSIERS (20) — matières variées
-- =================================================================
INSERT INTO dossiers (client_id, titre, couleur, statut, matiere, notes, date_ouverture, partie_adverse_nom, partie_adverse_prenom, partie_adverse_type, partie_adverse_entreprise, convention_type_honoraire, convention_montant_fixe, created_at, updated_at) VALUES

-- DROIT FAMILIAL (4)
(1,  'Divorce contentieux MARTIN / LEROUX',              '#EC4899','En cours','Droit familial',
 'Divorce pour faute. M. MARTIN conteste la prestation compensatoire exigée. Deux enfants mineurs concernés (7 et 11 ans). Audience JAF fixée.',
 '2026-01-15', 'LEROUX','Sophie','Personne physique',NULL,'horaire',NULL,NOW(),NOW()),

(3,  'Révision pension alimentaire MOREAU',              '#EC4899','En cours','Droit familial',
 'Demande de révision à la baisse suite à perte d''emploi du client. Ex-conjointe représentée par Me Arnaud.',
 '2026-02-03', 'MOREAU','Catherine','Personne physique',NULL,'fixe',1800.00,NOW(),NOW()),

(4,  'Succession ROUSSEAU — contestation testament',     '#EC4899','En cours','Droit familial',
 'Mme ROUSSEAU conteste le testament olographe de son père décédé le 12/01/2026. Patrimoine estimé à 480 000 €.',
 '2026-02-20', 'ROUSSEAU','Bernard','Personne physique',NULL,'pourcentage',NULL,NOW(),NOW()),

(7,  'Adoption plénière — dossier SIMON',               '#EC4899','En cours','Droit familial',
 'Adoption plénière d''un enfant de 4 ans par Mme SIMON et son conjoint. Dossier administratif complet constitué.',
 '2026-03-10', NULL,NULL,'Personne physique',NULL,'fixe',2500.00,NOW(),NOW()),

-- DROIT PÉNAL (4)
(6,  'Défense pénale PETIT — vol avec violence',        '#EF4444','En cours','Droit pénal',
 'Client mis en cause pour vol avec violence en réunion. Faits du 08/12/2025. Instruction en cours. Deux co-auteurs identifiés.',
 '2026-01-20', 'MINISTÈRE PUBLIC',NULL,'Personne morale',NULL,'horaire',NULL,NOW(),NOW()),

(9,  'GARNIER — conduite sous influence alcool',        '#EF4444','En cours','Droit pénal',
 'Taux d''alcoolémie 1,4 g/L. Comparution immédiate. Permis invalidé. Première infraction.',
 '2026-03-05', 'MINISTÈRE PUBLIC',NULL,'Personne morale',NULL,'fixe',1500.00,NOW(),NOW()),

(10, 'LEMAIRE — escroquerie bancaire en bande',         '#EF4444','En cours','Droit pénal',
 'Mise en examen pour escroquerie au faux virements bancaires. Préjudice estimé 320 000 €. Plusieurs victimes.',
 '2025-11-08', 'MINISTÈRE PUBLIC',NULL,'Personne morale',NULL,'horaire',NULL,NOW(),NOW()),

(8,  'CHEVALIER — fraude fiscale — mise en examen',     '#EF4444','En cours','Droit pénal',
 '⚠ CONFLIT POTENTIEL : M. CHEVALIER est partie adverse dans dossier Lambert Finances (dossier 11). Vérification effectuée le 2026-04-02. Représentation maintenue après analyse : dossiers distincts et non liés.',
 '2026-04-01', 'MINISTÈRE PUBLIC',NULL,'Personne morale',NULL,'horaire',NULL,NOW(),NOW()),

-- DROIT DES AFFAIRES (4)
(26, 'LEFEBVRE INDUSTRIES — rupture contrat commercial','#1E40AF','En cours','Droit des affaires',
 '⚠ CONFLIT IDENTIFIÉ : La partie adverse DUPONT Jean est également client du cabinet (dossier 14). Signalement effectué. Autorisation client confirmée par écrit.',
 '2026-01-08', 'DUPONT','Jean','Personne physique',NULL,'horaire',NULL,NOW(),NOW()),

(27, 'DUPONT BÂTIMENT — dissolution liquidation',      '#1E40AF','En cours','Droit des affaires',
 'Dissolution amiable de la SARL DUPONT BÂTIMENT. Liquidateur désigné. Passif résiduel à apurer.',
 '2026-02-14', NULL,NULL,'Personne physique',NULL,'fixe',3500.00,NOW(),NOW()),

(29, 'LAMBERT FINANCES — recouvrement créance',        '#1E40AF','En cours','Droit des affaires',
 '⚠ CONFLIT POTENTIEL : Partie adverse M. CHEVALIER Éric est client du cabinet (dossier 9). Analyse en cours.',
 '2026-01-22', 'CHEVALIER','Éric','Personne physique',NULL,'pourcentage',NULL,NOW(),NOW()),

(30, 'ROCHE CONSULTING — litige fournisseur',          '#1E40AF','En cours','Droit des affaires',
 'Litige avec fournisseur IT pour non-conformité de livraison logicielle. Mise en demeure envoyée. Arbitrage envisagé.',
 '2026-03-18', NULL,NULL,'Personne morale','SOFTEDGE TECHNOLOGIES SAS','fixe',2800.00,NOW(),NOW()),

-- DROIT SOCIAL (4)
(2,  'BERNARD — licenciement sans cause réelle',       '#F97316','En cours','Droit social',
 'Licenciement économique contesté. Ancienneté 8 ans. Saisine CPH Lyon. Audience de conciliation 15/06/2026.',
 '2026-02-01', NULL,NULL,'Personne morale','MANUFACTURE DU RHÔNE SAS','horaire',NULL,NOW(),NOW()),

(5,  'DUPONT Jean — accident du travail faute inexcusable','#F97316','En cours','Droit social',
 'Chute de hauteur sur chantier le 14/11/2025. ITT 45 jours. Employeur : SARL DUPONT BÂTIMENT (client 27). Faute inexcusable retenue par CARSAT.',
 '2026-01-10', NULL,NULL,'Personne morale','DUPONT BÂTIMENT SARL','horaire',NULL,NOW(),NOW()),

(12, 'ROBERT — harcèlement moral CPH Grenoble',       '#F97316','En cours','Droit social',
 'Harcèlement moral caractérisé par supérieur hiérarchique. Témoignages recueillis. Dépôt plainte pénale parallèle.',
 '2026-02-28', NULL,NULL,'Personne morale','ALPITEC SA','fixe',2200.00,NOW(),NOW()),

(21, 'DURAND Pierre — contentieux Conseil Nat. Ordre','#F97316','En cours','Droit social',
 'Plainte disciplinaire déposée par un patient pour manquement au secret médical. Comparution devant section disciplinaire CNO.',
 '2026-03-22', NULL,NULL,'Personne physique',NULL,'fixe',3000.00,NOW(),NOW()),

-- DROIT IMMOBILIER (2)
(28, 'SCI JARDINS FLEURIS — vice caché immeuble',     '#059669','En cours','Droit immobilier',
 'Infiltrations importantes découvertes 3 mois après acquisition. Expertise ordonnée. Vendeur assigné. Préjudice estimé 85 000 €.',
 '2026-02-10', 'MARCHETTI','Bruno','Personne physique',NULL,'pourcentage',NULL,NOW(),NOW()),

(4,  'ROUSSEAU — trouble de voisinage nuisances',     '#059669','En cours','Droit immobilier',
 'Nuisances sonores nocturnes répétées (activité commerciale non déclarée). Mise en demeure ignorée. Saisine TJ Bordeaux.',
 '2026-03-01', 'VENTURA','Carlos','Personne physique',NULL,'fixe',1800.00,NOW(),NOW()),

-- DROIT ADMINISTRATIF (2)
(16, 'MARCHAND — recours permis de construire refusé','#7C3AED','En cours','Droit administratif',
 'Extension maison refusée par maire. Recours gracieux rejeté. Recours contentieux TA Clermont-Ferrand.',
 '2026-02-25', NULL,NULL,'Personne morale','MAIRIE DE COURNON-D''AUVERGNE','fixe',2000.00,NOW(),NOW()),

-- DROIT INTERNATIONAL (1)
(26, 'LEFEBVRE INDUSTRIES — arbitrage international', '#06B6D4','En cours','Droit international',
 'Arbitrage CCI contre fournisseur espagnol (IBERICA METAL SL) pour non-respect clauses contractuelles. Siège arbitrage : Paris. Montant litige : 1 200 000 €.',
 '2026-01-30', NULL,NULL,'Personne morale','IBERICA METAL SL','horaire',NULL,NOW(),NOW());

-- =================================================================
-- PARTIES ADVERSES (détaillées)
-- =================================================================
INSERT INTO parties_adverses (dossier_id, nom, prenom, type_partie, entreprise, avocat_adverse, barreau_adverse, adresse, code_postal, ville, created_at, updated_at) VALUES
-- Dossier 1 : Divorce MARTIN
(1, 'LEROUX', 'Sophie', 'Personne physique', NULL, 'Me Arnaud BOUCHER', 'Lyon', '3 place Carnot', '37000', 'Tours', NOW(), NOW()),
-- Dossier 2 : Pension alimentaire
(2, 'MOREAU', 'Catherine', 'Personne physique', NULL, 'Me Isabelle RENAUD', 'Toulouse', '18 rue Saint-Exupéry', '31400', 'Toulouse', NOW(), NOW()),
-- Dossier 3 : Succession
(3, 'ROUSSEAU', 'Bernard', 'Personne physique', NULL, NULL, NULL, '7 rue de la Rivière', '33200', 'Bordeaux', NOW(), NOW()),
(3, 'ROUSSEAU', 'Michel', 'Personne physique', NULL, NULL, NULL, '2 allée des Ormes', '33700', 'Mérignac', NOW(), NOW()),
-- Dossier 5 : Vol avec violence PETIT
(5, NULL, NULL, 'Personne physique', NULL, NULL, NULL, NULL, NULL, NULL, NOW(), NOW()),
-- Dossier 9 : Rupture contrat LEFEBVRE ⚠ CONFLIT
(9, 'DUPONT', 'Jean', 'Personne physique', NULL, 'Me François KLEIN', 'Paris', '14 boulevard Haussmann', '75008', 'Paris', NOW(), NOW()),
-- Dossier 11 : Recouvrement LAMBERT ⚠ CONFLIT
(11, 'CHEVALIER', 'Éric', 'Personne physique', NULL, 'Me Lucie PICARD', 'Strasbourg', '33 rue Nationale', '67000', 'Strasbourg', NOW(), NOW()),
-- Dossier 13 : Licenciement BERNARD
(13, NULL, NULL, 'Personne morale', 'MANUFACTURE DU RHÔNE SAS', 'Me Pierre DUMAS', 'Lyon', '14 avenue Berthelot', '69007', 'Lyon', NOW(), NOW()),
-- Dossier 17 : Vice caché SCI
(17, 'MARCHETTI', 'Bruno', 'Personne physique', NULL, 'Me Sandra VIDAL', 'Nice', '5 villa des Orangers', '06100', 'Nice', NOW(), NOW()),
-- Dossier 18 : Trouble voisinage ROUSSEAU
(18, 'VENTURA', 'Carlos', 'Personne physique', NULL, NULL, NULL, '5 rue du Port', '33000', 'Bordeaux', NOW(), NOW()),
-- Dossier 20 : Arbitrage international LEFEBVRE
(20, NULL, NULL, 'Personne morale', 'IBERICA METAL SL', 'M. José GARCIA (avocat espagnol)', 'Madrid (Barreau)', 'Calle Mayor 47', '28013', 'Madrid', NOW(), NOW());

-- =================================================================
-- RENDEZ-VOUS (8 sur les 5 prochains jours)
-- =================================================================
INSERT INTO rendezvous (client_id, dossier_id, date, heure, duree, type, lieu, notes, created_at, updated_at) VALUES
(1,  1,  '2026-05-25', '09:00:00', 60,  'Consultation',        'Cabinet', 'Point d''étape avant audience JAF du 02/06. Préparer pièces complémentaires.', NOW(), NOW()),
(5,  14, '2026-05-25', '14:30:00', 45,  'Consultation',        'Cabinet', 'Réunion préparatoire audience prud''hommes. Revoir calcul préjudice.', NOW(), NOW()),
(26, 9,  '2026-05-26', '10:00:00', 90,  'Réunion',             'Cabinet', 'Réunion stratégique rupture contrat. Présence DG et DAF LEFEBVRE.', NOW(), NOW()),
(2,  13, '2026-05-26', '16:00:00', 60,  'Consultation',        'Cabinet', 'Préparation conciliation CPH. Documents à apporter : bulletins de salaire.', NOW(), NOW()),
(6,  5,  '2026-05-27', '09:30:00', 120, 'Audience',            'Tribunal correctionnel de Lille', 'Audience correctionnelle. Prévenu convoqué à 9h00 au greffe.', NOW(), NOW()),
(28, 17, '2026-05-27', '14:00:00', 60,  'Consultation',        'Cabinet', 'Réception rapport expertise judiciaire. Analyse avec le client.', NOW(), NOW()),
(29, 11, '2026-05-28', '11:00:00', 75,  'Réunion de synthèse', 'Cabinet', 'Bilan procédure recouvrement. Options : saisie ou protocole transactionnel.', NOW(), NOW()),
(4,  3,  '2026-05-29', '15:00:00', 90,  'Médiation',           'Chambre professionnelle de médiation - Bordeaux', 'Médiation succession ROUSSEAU. Présence des 3 héritiers prévue.', NOW(), NOW());

-- =================================================================
-- CHRONOLOGIES (3-5 évènements par dossier)
-- =================================================================
INSERT INTO chronologie (dossier_id, type, titre, description, date, auto, created_at) VALUES
-- Dossier 1 : Divorce MARTIN
(1,'systeme','Ouverture du dossier','Dossier créé suite à consultation du 15/01/2026. Convention honoraires signée.','2026-01-15 09:30:00',true,NOW()),
(1,'reunion','Première consultation','Entretien approfondi M. MARTIN. Recueil des faits et pièces. Stratégie définie : divorce pour faute.','2026-01-15 10:00:00',false,NOW()),
(1,'courrier','Mise en demeure adressée à Mme LEROUX','Mise en demeure de cesser les faits préjudiciables avant dépôt requête.','2026-01-28 00:00:00',false,NOW()),
(1,'acte','Requête en divorce déposée','Requête en divorce déposée au greffe du JAF de Lyon. N° RG : 26/00847.','2026-02-10 00:00:00',false,NOW()),
(1,'audience','Ordonnance de non-conciliation','Audience ONP : époux non réconciliés. Mesures provisoires fixées. Droit de garde partagé.','2026-03-18 14:00:00',false,NOW()),
-- Dossier 2 : Pension alimentaire
(2,'systeme','Ouverture du dossier','Dossier ouvert. Convention signée.','2026-02-03 10:00:00',true,NOW()),
(2,'appel','Appel client — baisse ressources','M. MOREAU confirme perte emploi le 01/02/2026. Documents justificatifs transmis.','2026-02-05 11:30:00',false,NOW()),
(2,'acte','Assignation en révision déposée','Assignation déposée au JAF de Toulouse. Audience fixée au 12/06/2026.','2026-02-20 00:00:00',false,NOW()),
(2,'email','Échange avec avocat partie adverse','Me RENAUD conteste la demande. Offre de compromis : -15 % au lieu de -40 %.','2026-03-10 09:00:00',false,NOW()),
-- Dossier 3 : Succession
(3,'systeme','Ouverture du dossier','Dossier successoral ouvert.','2026-02-20 14:00:00',true,NOW()),
(3,'reunion','Consultation initiale','Recueil testament et inventaire patrimoine. Évaluation préjudice Mme ROUSSEAU.','2026-02-20 15:00:00',false,NOW()),
(3,'document','Réception acte de décès et testament','Documents originaux reçus et archivés.','2026-02-25 00:00:00',false,NOW()),
(3,'courrier','Mise en demeure aux co-héritiers','Demande de rendez-vous pour règlement amiable.','2026-03-05 00:00:00',false,NOW()),
(3,'note','Expertise notariale en cours','Notaire Me PELLERIN mandaté pour évaluation immobilière.','2026-04-02 00:00:00',false,NOW()),
-- Dossier 5 : Vol avec violence PETIT
(5,'systeme','Ouverture du dossier','Dossier pénal ouvert suite GAV.','2026-01-20 18:00:00',true,NOW()),
(5,'reunion','Entretien garde à vue','M. PETIT placé en GAV. Assistance fournie. Audition libre accordée.','2026-01-20 19:30:00',false,NOW()),
(5,'note','Mise en examen confirmée','Ordonnance de renvoi devant le tribunal correctionnel de Lille rendue.','2026-02-12 00:00:00',false,NOW()),
(5,'document','Dossier de procédure reçu','3 tomes. Analyse en cours. Témoins identifiés.','2026-03-08 00:00:00',false,NOW()),
(5,'acte','Conclusions de mise en état déposées','Mémoire de défense sur la qualification et la culpabilité.','2026-04-15 00:00:00',false,NOW()),
-- Dossier 7 : Escroquerie LEMAIRE
(7,'systeme','Ouverture du dossier','Dossier pénal complexe. Instruction judiciaire en cours.','2025-11-08 00:00:00',true,NOW()),
(7,'reunion','Entretien en maison d''arrêt','M. LEMAIRE incarcéré. Entretien confidentiel.','2025-11-15 14:00:00',false,NOW()),
(7,'acte','Demande de mise en liberté déposée','Rejetée le 05/12/2025. Appel formé devant la chambre de l''instruction.','2025-12-01 00:00:00',false,NOW()),
(7,'decision','Liberté sous contrôle judiciaire obtenue','Bracelet électronique. Interdiction de contact avec les victimes.','2026-01-20 00:00:00',false,NOW()),
(7,'note','Reconstitution des flux financiers','Analyse expertale des 47 virements suspects en cours.','2026-03-15 00:00:00',false,NOW()),
-- Dossier 8 : Fraude fiscale CHEVALIER ⚠ CONFLIT
(8,'systeme','Ouverture du dossier','Dossier pénal fiscal. Vérification conflits réalisée.','2026-04-01 09:00:00',true,NOW()),
(8,'note','⚠ Vérification conflit d''intérêts','M. CHEVALIER Éric identifié comme partie adverse dans dossier Lambert Finances. Analyse : dossiers non connexes. Représentation autorisée. Accord écrit clients obtenus.','2026-04-02 10:30:00',false,NOW()),
(8,'reunion','Consultation initiale','Présentation dossier fiscal. Mise en examen du 28/03/2026.','2026-04-03 14:00:00',false,NOW()),
(8,'document','Rapport DGFIP reçu','Redressement fiscal 2020-2023. Montant : 187 000 € + pénalités.','2026-04-18 00:00:00',false,NOW()),
-- Dossier 9 : Rupture contrat LEFEBVRE ⚠ CONFLIT
(9,'systeme','Ouverture du dossier','Dossier ouvert. Conflit d''intérêts signalé et géré.','2026-01-08 09:00:00',true,NOW()),
(9,'note','⚠ Conflit d''intérêts identifié','M. DUPONT Jean (partie adverse) est client du cabinet (dossier 14). Conflit notifié aux deux parties. M. DUPONT a confirmé ne pas s''opposer à la représentation dans ce litige commercial distinct.','2026-01-09 11:00:00',false,NOW()),
(9,'courrier','Mise en demeure DUPONT Jean','MED formelle pour exécution contrat ou versement dommages 340 000 €.','2026-01-20 00:00:00',false,NOW()),
(9,'email','Réponse négative partie adverse','Me KLEIN refuse tout règlement amiable. Procédure judiciaire engagée.','2026-02-03 09:30:00',false,NOW()),
(9,'acte','Assignation déposée TJ Paris','Assignation déposée. Audience de mise en état fixée au 18/09/2026.','2026-02-25 00:00:00',false,NOW()),
-- Dossier 11 : Recouvrement LAMBERT ⚠ CONFLIT
(11,'systeme','Ouverture du dossier','Dossier recouvrement créance.','2026-01-22 10:00:00',true,NOW()),
(11,'note','⚠ Conflit potentiel identifié','M. CHEVALIER Éric (débiteur) est client du cabinet dossier 9 (fraude fiscale). Analyse en cours.','2026-01-23 09:00:00',false,NOW()),
(11,'courrier','Mise en demeure M. CHEVALIER','Créance de 94 500 € TTC. Délai de paiement 15 jours.','2026-02-01 00:00:00',false,NOW()),
(11,'note','Décision : conflits gérés','Après analyse, les deux dossiers sont distincts et non connexes. Accord écrit des deux parties obtenu le 25/01/2026.','2026-02-05 14:00:00',false,NOW()),
(11,'acte','Requête en injonction de payer','Requête déposée au TJ de Paris.','2026-03-10 00:00:00',false,NOW()),
-- Dossier 13 : Licenciement BERNARD
(13,'systeme','Ouverture du dossier','Dossier prud''homal ouvert.','2026-02-01 10:00:00',true,NOW()),
(13,'document','Réception lettre de licenciement','Lettre de licenciement économique reçue et analysée. Motif contesté.','2026-02-05 00:00:00',false,NOW()),
(13,'acte','Saisine CPH Lyon','Requête introductive d''instance déposée.','2026-02-20 00:00:00',false,NOW()),
(13,'audience','Audience de conciliation','Tentative de conciliation échouée. Renvoi bureau de jugement.','2026-04-10 09:00:00',false,NOW()),
(13,'note','Échange pièces adverses','Reçu conclusions en défense de Me DUMAS.','2026-05-15 00:00:00',false,NOW()),
-- Dossier 14 : Accident travail DUPONT
(14,'systeme','Ouverture du dossier','Dossier accident travail.','2026-01-10 10:00:00',true,NOW()),
(14,'document','Rapport CARSAT reçu','Faute inexcusable retenue à l''encontre de l''employeur DUPONT BÂTIMENT.','2026-01-20 00:00:00',false,NOW()),
(14,'acte','Assignation CPAM et employeur','Demande de reconnaissance faute inexcusable. TASS de Strasbourg.','2026-02-15 00:00:00',false,NOW()),
(14,'reunion','Réunion d''expertise médicale','Évaluation séquelles permanentes. ITT : 45 jours. IPP estimée 12 %.','2026-03-22 10:00:00',false,NOW()),
-- Dossier 17 : Vice caché SCI
(17,'systeme','Ouverture du dossier','Dossier immobilier vice caché.','2026-02-10 09:00:00',true,NOW()),
(17,'document','Expertise amiable reçue','Rapport expert privé confirme les infiltrations antérieures à la vente.','2026-02-18 00:00:00',false,NOW()),
(17,'acte','Assignation en référé expertise','Demande d''expertise judiciaire contradictoire. Audience référé TJ Nice 15/03/2026.','2026-03-01 00:00:00',false,NOW()),
(17,'decision','Ordonnance expertise judiciaire','Expert judiciaire désigné : M. Philippe COSTA. Réunion expertise 28/05/2026.','2026-03-15 14:00:00',false,NOW()),
-- Dossier 19 : Permis construire MARCHAND
(19,'systeme','Ouverture du dossier','Dossier administratif ouvert.','2026-02-25 10:00:00',true,NOW()),
(19,'courrier','Recours gracieux déposé','Recours gracieux auprès du maire. Délai réponse 2 mois.','2026-03-01 00:00:00',false,NOW()),
(19,'decision','Recours gracieux rejeté','Décision implicite de rejet au 01/05/2026.','2026-05-02 00:00:00',false,NOW()),
(19,'acte','Recours contentieux TA','Requête déposée devant TA Clermont-Ferrand.','2026-05-20 00:00:00',false,NOW());

-- =================================================================
-- ÉCHÉANCES par dossier
-- =================================================================
INSERT INTO echeances (dossier_id, type, date, description, statut, created_at, updated_at) VALUES
-- Dossier 1 : Divorce
(1,'Audience','2026-06-02','Audience JAF Lyon — fond du divorce','À venir',NOW(),NOW()),
(1,'Délai de conclusions','2026-05-28','Dépôt conclusions récapitulatives époux MARTIN','À venir',NOW(),NOW()),
(1,'Dépôt de pièces','2026-05-26','Transmission pièces à Me BOUCHER','À venir',NOW(),NOW()),
-- Dossier 2 : Pension
(2,'Audience','2026-06-12','Audience JAF Toulouse — révision pension','À venir',NOW(),NOW()),
(2,'Délai de conclusions','2026-05-30','Conclusions en réponse à me RENAUD','À venir',NOW(),NOW()),
-- Dossier 3 : Succession
(3,'Expertise','2026-06-15','Dépôt rapport expertise notariale','À venir',NOW(),NOW()),
(3,'Audience','2026-07-08','Audience TJ Bordeaux — contestation testament','À venir',NOW(),NOW()),
-- Dossier 5 : Vol PETIT
(5,'Audience','2026-06-20','Audience correctionnelle TGI Lille','À venir',NOW(),NOW()),
(5,'Délai de conclusions','2026-06-05','Conclusions en défense PETIT','À venir',NOW(),NOW()),
-- Dossier 7 : Escroquerie LEMAIRE
(7,'Audience','2026-07-14','Audience chambre correctionnelle Paris — escroquerie','À venir',NOW(),NOW()),
(7,'Délai de recours','2026-06-01','Délai appel si condamnation en 1ère instance','À venir',NOW(),NOW()),
-- Dossier 9 : Rupture contrat LEFEBVRE
(9,'Audience','2026-09-18','Audience mise en état TJ Paris','À venir',NOW(),NOW()),
(9,'Délai de conclusions','2026-07-15','Conclusions au fond LEFEBVRE INDUSTRIES','À venir',NOW(),NOW()),
-- Dossier 11 : Recouvrement LAMBERT
(11,'Audience','2026-06-25','Audience injonction de payer TJ Paris','À venir',NOW(),NOW()),
-- Dossier 13 : Licenciement BERNARD
(13,'Audience','2026-06-18','Audience bureau de jugement CPH Lyon','À venir',NOW(),NOW()),
(13,'Délai de conclusions','2026-05-30','Dernières conclusions Mme BERNARD','À venir',NOW(),NOW()),
-- Dossier 14 : Accident travail DUPONT
(14,'Audience','2026-07-02','Audience TASS Strasbourg — faute inexcusable','À venir',NOW(),NOW()),
(14,'Expertise','2026-06-10','Expertise médicale séquelles permanentes','À venir',NOW(),NOW()),
-- Dossier 17 : Vice caché SCI
(17,'Expertise','2026-05-28','Réunion expertise judiciaire sur site','À venir',NOW(),NOW()),
(17,'Délai de conclusions','2026-08-01','Dépôt conclusions après rapport expert','À venir',NOW(),NOW()),
-- Dossier 18 : Trouble voisinage ROUSSEAU
(18,'Audience','2026-06-30','Audience référé TJ Bordeaux','À venir',NOW(),NOW()),
-- Dossier 19 : Permis construire MARCHAND
(19,'Délai de recours','2026-11-20','Délai instruction TA — mémoire en réplique','À venir',NOW(),NOW()),
(19,'Audience','2026-12-10','Audience TA Clermont-Ferrand','À venir',NOW(),NOW()),
-- Dossier 20 : Arbitrage international LEFEBVRE
(20,'Dépôt de pièces','2026-07-01','Dépôt mémoire d''arbitrage CCI','À venir',NOW(),NOW()),
(20,'Audience','2026-10-15','Audience arbitrale CCI Paris','À venir',NOW(),NOW());

-- =================================================================
-- MODÈLES D'ACTES (6)
-- =================================================================
INSERT INTO modeles_actes (nom, categorie, contenu, created_at, updated_at) VALUES

('Mise en demeure générale', 'Correspondance',
'**MISE EN DEMEURE**

**Cabinet Maître Dupuis & Associés**
15 rue de la Paix — 75002 Paris
Tél : 01 42 68 47 23

---

**{{CLIENT_NOM}}**
{{CLIENT_ADRESSE}}

Paris, le {{DATE}}

**MISE EN DEMEURE**
*(valant mise en demeure au sens de l''article 1344 du Code civil)*

Maître Sophie DUPUIS, Avocat au Barreau de Paris, agissant au nom et pour le compte de {{CLIENT_PRENOM}} {{CLIENT_NOM}},

**A l''attention de : {{PARTIE_ADVERSE_NOM}}**

Monsieur / Madame,

Nous vous mettons en demeure de **{{OBJET_MED}}** dans un délai de **{{DELAI}} jours** à compter de la réception du présent courrier.

À défaut, nous serons contraints d''engager toute procédure judiciaire utile à la préservation des droits de notre client, sans autre avis de notre part.

Nous vous prions d''agréer, Madame, Monsieur, l''expression de nos salutations distinguées.

**Maître Sophie DUPUIS**
Avocat au Barreau de Paris',
NOW(), NOW()),

('Convention honoraires — taux horaire', 'Conventions',
'**CONVENTION D''HONORAIRES**
*(taux horaire — article 10 de la loi du 31 décembre 1971)*

**ENTRE :**
**Cabinet Maître Dupuis & Associés**, représenté par Maître Sophie DUPUIS, Avocat au Barreau de Paris (N° P-2847), 15 rue de la Paix, 75002 Paris

**ET :**
{{CLIENT_CIVILITE}} {{CLIENT_PRENOM}} {{CLIENT_NOM}}
{{CLIENT_ADRESSE}}, {{CLIENT_CODE_POSTAL}} {{CLIENT_VILLE}}

---

**OBJET DE LA MISSION :** {{OBJET_MISSION}}

**HONORAIRES :**
Les honoraires sont fixés à **{{TAUX_HORAIRE}} € HT / heure** de travail effectif.

Une provision de **{{PROVISION}} € TTC** est demandée à la signature.

**FRAIS ET DÉBOURS :**
Les frais (copies, huissiers, experts) sont refacturés au coût réel.

**DATE ET SIGNATURE :**
Fait à Paris, le {{DATE}}

Maître Sophie DUPUIS                    Le client
*(signature)*                           *(signature précédée de "Lu et approuvé")*',
NOW(), NOW()),

('Convention honoraires — forfait', 'Conventions',
'**CONVENTION D''HONORAIRES**
*(forfait — article 10 de la loi du 31 décembre 1971)*

**ENTRE :**
**Cabinet Maître Dupuis & Associés**, Maître Sophie DUPUIS, Avocat au Barreau de Paris

**ET :**
{{CLIENT_CIVILITE}} {{CLIENT_PRENOM}} {{CLIENT_NOM}}

---

**OBJET DE LA MISSION :** {{OBJET_MISSION}}

**HONORAIRES FORFAITAIRES :**
Honoraires convenus : **{{MONTANT_FORFAIT}} € HT**
TVA 20 % : **{{TVA}} €**
**Total TTC : {{TOTAL_TTC}} €**

Ce forfait comprend : consultations, rédaction d''actes, représentation en première instance.

Ne sont pas inclus : appel, cassation, procédures d''exécution.

Fait à Paris, le {{DATE}}

Maître Sophie DUPUIS                    Le client',
NOW(), NOW()),

('Assignation en divorce', 'Actes de procédure',
'**ASSIGNATION EN DIVORCE**
*(articles 251 et suivants du Code civil)*

**À L''ATTENTION DE :**
Monsieur le Président du Tribunal Judiciaire de {{VILLE_TJ}}
Juge aux Affaires Familiales

---

**DEMANDEUR :** {{CLIENT_CIVILITE}} {{CLIENT_PRENOM}} {{CLIENT_NOM}}, né(e) le {{DATE_NAISSANCE}}, demeurant {{CLIENT_ADRESSE}}

Ayant pour avocat : Maître Sophie DUPUIS, Cabinet Dupuis & Associés, 15 rue de la Paix, 75002 Paris

**DÉFENDEUR :** {{PARTIE_ADVERSE_CIVILITE}} {{PARTIE_ADVERSE_PRENOM}} {{PARTIE_ADVERSE_NOM}}, né(e) le {{PA_DATE_NAISSANCE}}, demeurant {{PA_ADRESSE}}

---

**EXPOSÉ DES FAITS ET MOYENS :**

Les époux se sont mariés le {{DATE_MARIAGE}} à {{LIEU_MARIAGE}}.

De leur union sont issus {{NB_ENFANTS}} enfant(s) : {{ENFANTS}}.

Les époux sont en instance de divorce depuis l''ordonnance de non-conciliation rendue le {{DATE_ONC}}.

**DEMANDES :**
- Prononcer le divorce aux torts {{TORTS}}
- Fixer la résidence des enfants {{RESIDENCES}}
- Condamner {{PARTIE_ADVERSE_NOM}} au versement d''une prestation compensatoire de {{MONTANT_PRESTATION}} €

**PAR CES MOTIFS :**
Le demandeur vous prie de bien vouloir faire droit à ses demandes.

Fait à Paris, le {{DATE}}
Maître Sophie DUPUIS',
NOW(), NOW()),

('Conclusions de fond — défense', 'Actes de procédure',
'**CONCLUSIONS**

**Affaire :** {{REF_DOSSIER}}
**Audience :** {{DATE_AUDIENCE}}
**Juridiction :** {{JURIDICTION}}

---

**POUR :** {{CLIENT_CIVILITE}} {{CLIENT_PRENOM}} {{CLIENT_NOM}}
Représenté par Maître Sophie DUPUIS

**CONTRE :** {{PARTIE_ADVERSE_NOM}}

---

**I — RAPPEL DES FAITS**

{{RAPPEL_FAITS}}

**II — DISCUSSION**

**A) Sur la recevabilité**

L''action de {{PARTIE_ADVERSE_NOM}} est {{RECEVABILITE}}.

**B) Sur le fond**

{{DEVELOPPEMENT_FOND}}

**III — PAR CES MOTIFS**

Plaise au Tribunal de :
- DÉBOUTER {{PARTIE_ADVERSE_NOM}} de l''ensemble de ses demandes
- CONDAMNER {{PARTIE_ADVERSE_NOM}} à payer à {{CLIENT_NOM}} la somme de {{MONTANT}} €
- CONDAMNER {{PARTIE_ADVERSE_NOM}} aux dépens et à {{ARTICLE_700}} € au titre de l''article 700 CPC

**Sous toutes réserves**

Maître Sophie DUPUIS
Avocat au Barreau de Paris',
NOW(), NOW()),

('Protocole transactionnel', 'Transactions',
'**PROTOCOLE D''ACCORD TRANSACTIONNEL**
*(article 2044 du Code civil)*

**ENTRE LES SOUSSIGNÉS :**

**D''une part :**
{{PARTIE_1_NOM}}, représenté par Maître Sophie DUPUIS

**D''autre part :**
{{PARTIE_2_NOM}}, représenté par {{AVOCAT_ADVERSE}}

---

**Il a été convenu ce qui suit :**

**ARTICLE 1 — OBJET**
Le présent protocole met fin définitivement au litige opposant les parties dans l''affaire {{REF_LITIGE}}.

**ARTICLE 2 — OBLIGATIONS**
{{PARTIE_2_NOM}} s''engage à verser à {{PARTIE_1_NOM}} la somme de **{{MONTANT}} €** dans un délai de {{DELAI}} jours.

**ARTICLE 3 — RENONCIATION**
En contrepartie du paiement, les parties renoncent mutuellement à toute action, réclamation ou recours liés au présent litige.

**ARTICLE 4 — CONFIDENTIALITÉ**
Le présent accord est strictement confidentiel.

**Fait à Paris, le {{DATE}}, en deux exemplaires originaux.**

{{PARTIE_1_NOM}}                    {{PARTIE_2_NOM}}
*(signature)*                        *(signature)*',
NOW(), NOW());

-- =================================================================
-- ACTES GÉNÉRÉS (liés aux dossiers)
-- =================================================================
INSERT INTO actes_generes (modele_id, dossier_id, client_id, nom, contenu, date_generation, statut) VALUES

(1, 9, 26, 'Mise en demeure DUPONT Jean — rupture contrat',
'**MISE EN DEMEURE**

Cabinet Maître Dupuis & Associés
15 rue de la Paix — 75002 Paris

LEFEBVRE INDUSTRIES SAS
47 rue de Bercy, 75012 Paris

Paris, le 20 janvier 2026

**A l''attention de : M. DUPONT Jean**
14 boulevard Haussmann, 75008 Paris

Maître Sophie DUPUIS, agissant au nom et pour le compte de la SAS LEFEBVRE INDUSTRIES,

Nous vous mettons en demeure de procéder au règlement de la somme de **340 000 € TTC** correspondant aux pénalités de rupture abusive du contrat du 15/03/2025, dans un délai de **15 jours** à compter de la réception du présent courrier.

À défaut, nous serons contraints d''engager une procédure devant le Tribunal Judiciaire de Paris.

Maître Sophie DUPUIS
Avocat au Barreau de Paris',
'2026-01-20 15:00:00', 'Signé'),

(2, 1, 1, 'Convention honoraires — M. MARTIN Antoine — Divorce',
'**CONVENTION D''HONORAIRES — Taux horaire**

Cabinet Maître Dupuis & Associés / M. MARTIN Antoine

Objet : Procédure en divorce contentieux devant le JAF de Lyon

Taux horaire : **250 € HT / heure**
Provision initiale : **2 500 € TTC**

Fait à Paris, le 15 janvier 2026

Maître Sophie DUPUIS ✓          M. MARTIN Antoine ✓',
'2026-01-15 11:00:00', 'Signé'),

(3, 5, 6, 'Convention honoraires — M. PETIT Antoine — Défense pénale',
'**CONVENTION D''HONORAIRES — Forfait**

Cabinet Maître Dupuis & Associés / M. PETIT Antoine

Objet : Défense pénale — vol avec violence — TGI Lille

Honoraires forfaitaires 1ère instance : **4 500 € HT**
TVA 20 % : 900 €
Total TTC : **5 400 €**

Fait à Paris, le 20 janvier 2026

Maître Sophie DUPUIS ✓          M. PETIT Antoine ✓',
'2026-01-20 20:00:00', 'Signé'),

(4, 1, 1, 'Assignation en divorce — MARTIN c/ LEROUX',
'**ASSIGNATION EN DIVORCE**

Tribunal Judiciaire de Lyon — JAF

Demandeur : M. MARTIN Antoine, né le 12/04/1981 à Clermont-Ferrand, demeurant 8 avenue Victor Hugo, 69006 Lyon

Défendeur : Mme LEROUX Sophie, née le 07/09/1983 à Tours, demeurant 3 place Carnot, 37000 Tours

Les époux se sont mariés le 14 juin 2008 à Lyon.
De leur union sont issus 2 enfants : Emma, 7 ans, et Lucas, 11 ans.

Faits : comportement injurieux et violent de Mme LEROUX documenté depuis 2024.

Demandes : divorce aux torts exclusifs de Mme LEROUX, résidence habituelle des enfants chez M. MARTIN, prestation compensatoire rejetée.

Fait à Paris, le 10 février 2026
Maître Sophie DUPUIS',
'2026-02-10 14:00:00', 'Signé'),

(5, 13, 2, 'Conclusions en défense CPH Lyon — Mme BERNARD',
'**CONCLUSIONS**

Affaire : BERNARD c/ MANUFACTURE DU RHÔNE SAS
Audience : 18 juin 2026
CPH Lyon

Pour : Mme BERNARD Claire

I — FAITS
Mme BERNARD a été employée pendant 8 ans au sein de la MANUFACTURE DU RHÔNE SAS. Son licenciement du 15/01/2026 est motivé par une prétendue cause économique.

II — DISCUSSION
A) La cause économique n''est pas établie : le groupe a enregistré une progression de 12 % de son CA en 2025.
B) Les critères d''ordre de licenciement n''ont pas été respectés.
C) Le préjudice subi s''élève a minima à 32 000 € (8 mois de salaire).

III — PAR CES MOTIFS
- Débouter la MANUFACTURE DU RHÔNE de ses demandes
- Condamner la société à payer 32 000 € de dommages-intérêts
- 2 500 € article 700 CPC

Maître Sophie DUPUIS',
'2026-05-15 16:00:00', 'Validé'),

(6, 12, 30, 'Protocole transactionnel — ROCHE CONSULTING c/ SOFTEDGE',
'**PROTOCOLE D''ACCORD TRANSACTIONNEL**

Entre :
- ROCHE CONSULTING EURL (partie 1), représentée par Maître DUPUIS
- SOFTEDGE TECHNOLOGIES SAS (partie 2), représentée par Me GROS

Objet : Litige livraison logicielle non conforme — contrat du 10/10/2025

ARTICLE 2 : SOFTEDGE versera à ROCHE CONSULTING la somme de **18 500 €** dans un délai de 30 jours.
ARTICLE 3 : Les parties renoncent à tout recours ultérieur.

Fait à Paris, le 22 avril 2026

ROCHE CONSULTING ✓              SOFTEDGE TECHNOLOGIES ✓',
'2026-04-22 10:00:00', 'Signé');

-- =================================================================
-- FACTURES (15) + LIGNES + PAIEMENTS
-- =================================================================
INSERT INTO factures (numero, client_id, dossier_id, type_document, montant_ht, taux_tva, provision, date_emission, statut, mode_reglement, nombre_relances, created_at, updated_at) VALUES
('FAC-2026-001', 1,  1,  'Note d''honoraires', 2500.00, 20, 0, '2026-01-15', 'Payée',                'Virement',       0, NOW(), NOW()),
('FAC-2026-002', 6,  5,  'Note d''honoraires', 3750.00, 20, 0, '2026-01-22', 'Payée',                'Virement',       0, NOW(), NOW()),
('FAC-2026-003', 26, 9,  'Note d''honoraires', 4200.00, 20, 0, '2026-02-01', 'Partiellement payée', 'Virement',       0, NOW(), NOW()),
('FAC-2026-004', 2,  13, 'Note d''honoraires', 1800.00, 20, 0, '2026-02-05', 'En cours',            'Virement',       0, NOW(), NOW()),
('FAC-2026-005', 5,  14, 'Note d''honoraires', 2200.00, 20, 0, '2026-02-12', 'Payée',               'Chèque',         0, NOW(), NOW()),
('FAC-2026-006', 3,  2,  'Provision',          900.00,  20, 0, '2026-02-05', 'Payée',               'Virement',       0, NOW(), NOW()),
('FAC-2026-007', 28, 17, 'Note d''honoraires', 3600.00, 20, 0, '2026-03-01', 'En cours',            'Virement',       0, NOW(), NOW()),
('FAC-2026-008', 29, 11, 'Note d''honoraires', 5800.00, 20, 0, '2026-03-10', 'En retard',           'Virement',       1, NOW(), NOW()),
('FAC-2026-009', 12, 15, 'Note d''honoraires', 1100.00, 20, 0, '2026-03-15', 'Payée',               'Espèces',        0, NOW(), NOW()),
('FAC-2026-010', 30, 12, 'Note d''honoraires', 2800.00, 20, 0, '2026-04-01', 'Payée',               'Virement',       0, NOW(), NOW()),
('FAC-2026-011', 10, 7,  'Note d''honoraires', 6500.00, 20, 0, '2026-04-05', 'Partiellement payée', 'Virement',       0, NOW(), NOW()),
('FAC-2026-012', 4,  3,  'Note d''honoraires', 2000.00, 20, 0, '2026-04-20', 'En cours',            'Chèque',         0, NOW(), NOW()),
('FAC-2026-013', 26, 20, 'Note d''honoraires', 8500.00, 20, 0, '2026-05-02', 'En cours',            'Virement',       0, NOW(), NOW()),
('FAC-2026-014', 16, 19, 'Note d''honoraires', 1000.00, 20, 0, '2026-05-10', 'En cours',            'Virement',       0, NOW(), NOW()),
('FAC-2026-015', 1,  1,  'Note d''honoraires', 1750.00, 20, 0, '2026-05-20', 'En cours',            'Virement',       0, NOW(), NOW());

-- LIGNES DE FACTURES
INSERT INTO lignes_facture (facture_id, position, description, quantite, prix_unitaire, unite) VALUES
-- FAC-001
(1, 1, 'Consultation initiale et analyse du dossier divorce', 2.00, 250.00, 'heure'),
(1, 2, 'Rédaction requête en divorce', 3.00, 250.00, 'heure'),
(1, 3, 'Assistance audience ONP', 4.00, 250.00, 'heure'),
-- FAC-002
(2, 1, 'Assistance garde à vue', 4.00, 250.00, 'heure'),
(2, 2, 'Analyse dossier de procédure (3 tomes)', 6.00, 250.00, 'heure'),
(2, 3, 'Rédaction conclusions de mise en état', 5.00, 250.00, 'heure'),
-- FAC-003
(3, 1, 'Analyse contrat commercial et préjudice', 5.00, 250.00, 'heure'),
(3, 2, 'Rédaction et envoi mise en demeure', 2.00, 250.00, 'heure'),
(3, 3, 'Rédaction assignation TJ Paris', 6.00, 250.00, 'heure'),
(3, 4, 'Frais de signification huissier', 1.00, 350.00, 'forfait'),
-- FAC-004
(4, 1, 'Consultation initiale droit du travail', 2.00, 250.00, 'heure'),
(4, 2, 'Analyse lettre de licenciement et pièces', 3.00, 250.00, 'heure'),
(4, 3, 'Saisine CPH Lyon', 1.00, 300.00, 'forfait'),
-- FAC-005
(5, 1, 'Consultation initiale accident de travail', 2.00, 250.00, 'heure'),
(5, 2, 'Analyse rapport CARSAT', 2.00, 250.00, 'heure'),
(5, 3, 'Assignation CPAM et employeur', 4.00, 250.00, 'heure'),
-- FAC-007
(7, 1, 'Analyse dossier expertise immobilière', 4.00, 250.00, 'heure'),
(7, 2, 'Assignation en référé expertise', 3.00, 250.00, 'heure'),
(7, 3, 'Participation réunion expertise judiciaire', 5.00, 250.00, 'heure'),
-- FAC-008
(8, 1, 'Analyse créance et dossier débiteur', 3.00, 250.00, 'heure'),
(8, 2, 'Rédaction mise en demeure',              2.00, 250.00, 'heure'),
(8, 3, 'Requête injonction de payer',             4.00, 250.00, 'heure'),
(8, 4, 'Suivi procédure et relances',             3.00, 250.00, 'heure'),
(8, 5, 'Frais et débours divers',                 1.00, 550.00, 'forfait'),
-- FAC-011
(11, 1, 'Analyse dossier instruction pénale', 8.00, 250.00, 'heure'),
(11, 2, 'Demande mise en liberté + appel',    6.00, 250.00, 'heure'),
(11, 3, 'Réunion expertise financière',       5.00, 250.00, 'heure'),
(11, 4, 'Déplacements maison d''arrêt (x3)', 1.00, 500.00, 'forfait'),
-- FAC-013
(13, 1, 'Consultation arbitrage international CCI', 4.00, 250.00, 'heure'),
(13, 2, 'Rédaction mémoire arbitral',               10.00, 250.00, 'heure'),
(13, 3, 'Coordination avec expert technique',       3.00, 250.00, 'heure'),
(13, 4, 'Frais d''arbitrage CCI (avance)',          1.00, 3500.00, 'forfait');

-- PAIEMENTS (pour factures payées et partielles)
INSERT INTO paiements (facture_id, date_paiement, montant, mode_reglement, reference, created_at) VALUES
-- FAC-001 payée (3000 TTC)
(1, '2026-01-20', 3000.00, 'Virement', 'VIR-2026-0120-MARTIN', NOW()),
-- FAC-002 payée (4500 TTC)
(2, '2026-02-01', 4500.00, 'Virement', 'VIR-2026-0201-PETIT', NOW()),
-- FAC-003 partiellement payée (TTC=5040, payé 2500)
(3, '2026-02-15', 2500.00, 'Virement', 'VIR-2026-0215-LEFEBVRE', NOW()),
-- FAC-005 payée (2640 TTC)
(5, '2026-02-20', 2640.00, 'Chèque', 'CHQ-847523', NOW()),
-- FAC-006 payée (1080 TTC)
(6, '2026-02-10', 1080.00, 'Virement', 'VIR-2026-0210-MOREAU', NOW()),
-- FAC-009 payée (1320 TTC)
(9, '2026-03-20', 1320.00, 'Espèces', 'ESP-20032026', NOW()),
-- FAC-010 payée (3360 TTC)
(10, '2026-04-10', 3360.00, 'Virement', 'VIR-2026-0410-ROCHE', NOW()),
-- FAC-011 partiellement payée (TTC=7800, payé 4000)
(11, '2026-04-15', 4000.00, 'Virement', 'VIR-2026-0415-LEMAIRE-ACOMPTE', NOW());

-- =================================================================
-- VÉRIFICATIONS CONFLITS D'INTÉRÊTS
-- =================================================================
INSERT INTO verifications_conflit (dossier_id, client_id, partie_adverse_nom, contexte, resultat, date_verification) VALUES

-- Dossier 9 (LEFEBVRE c/ DUPONT Jean) — CONFLIT DÉTECTÉ
(9, 26, 'DUPONT Jean',
 'Vérification avant acceptation dossier LEFEBVRE INDUSTRIES — rupture contrat commercial. Partie adverse : M. DUPONT Jean.',
 '{"aConflits":true,"conflits":[{"type":"Client actif","dossier_id":14,"dossier_titre":"DUPONT Jean — accident du travail faute inexcusable","client_id":5,"client_nom":"DUPONT Jean","detail":"M. DUPONT Jean est actuellement client du cabinet dans le dossier 14 (accident de travail). Il est ici partie adverse dans un litige commercial distinct. Conflit signalé aux deux parties. Accord écrit obtenu le 09/01/2026. Représentation maintenue."}]}',
 '2026-01-09 11:00:00'),

-- Dossier 11 (LAMBERT c/ CHEVALIER) — CONFLIT DÉTECTÉ
(11, 29, 'CHEVALIER Éric',
 'Vérification avant acceptation dossier LAMBERT FINANCES — recouvrement créance. Débiteur : M. CHEVALIER Éric.',
 '{"aConflits":true,"conflits":[{"type":"Client actif","dossier_id":8,"dossier_titre":"CHEVALIER — fraude fiscale — mise en examen","client_id":8,"client_nom":"CHEVALIER Éric","detail":"M. CHEVALIER Éric est client actif du cabinet dans le dossier pénal 8 (fraude fiscale). Il est ici débiteur/partie adverse dans une procédure de recouvrement civil. Après analyse, les deux affaires sont totalement distinctes et non connexes. Accord écrit des deux parties obtenu le 25/01/2026. Représentation maintenue dans les deux dossiers."}]}',
 '2026-01-23 09:30:00'),

-- Dossier 1 (MARTIN c/ LEROUX) — Pas de conflit
(1, 1, 'LEROUX Sophie',
 'Vérification standard avant ouverture dossier divorce MARTIN.',
 '{"aConflits":false,"conflits":[]}',
 '2026-01-15 09:00:00'),

-- Dossier 14 (DUPONT Jean) — Pas de conflit côté client
(14, 5, 'DUPONT BÂTIMENT SARL',
 'Vérification conflits dossier accident travail DUPONT Jean. Employeur adverse : SARL DUPONT BÂTIMENT (client 27).',
 '{"aConflits":true,"conflits":[{"type":"Client entreprise liée","dossier_id":10,"dossier_titre":"DUPONT BÂTIMENT — dissolution liquidation","client_id":27,"client_nom":"DUPONT BÂTIMENT SARL","detail":"La SARL DUPONT BÂTIMENT (employeur adverse) est également cliente du cabinet dans le dossier de dissolution (dossier 10). Conflit d''intérêts structurel. DÉCISION : retrait du dossier de dissolution ou maintien après renonciation. M. DUPONT Jean informé. Accord obtenu : le cabinet conserve les deux mandats, les dossiers étant de nature strictement distincte."}]}',
 '2026-01-11 14:00:00');

COMMIT;
