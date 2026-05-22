<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration complète CRM Cabinet d'Avocat
 * Crée tous les types énumérés PostgreSQL et les tables.
 */
final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma complet CRM Cabinet d\'Avocat';
    }

    public function up(Schema $schema): void
    {
        // ============================================================
        // TYPES ÉNUMÉRÉS
        // ============================================================

        $this->addSql("CREATE TYPE type_client_enum AS ENUM ('Particulier', 'Professionnel', 'Entreprise')");
        $this->addSql("CREATE TYPE statut_dossier_enum AS ENUM ('En cours', 'Clos', 'Suspendu', 'Archivé')");
        $this->addSql("CREATE TYPE matiere_enum AS ENUM ('Droit familial', 'Droit pénal', 'Droit des affaires', 'Droit social', 'Droit immobilier', 'Droit administratif', 'Droit international', 'Autres')");
        $this->addSql("CREATE TYPE statut_facture_enum AS ENUM ('En cours', 'Payée', 'Partiellement payée', 'Annulée', 'En retard')");
        $this->addSql("CREATE TYPE type_document_enum AS ENUM ('Note d''honoraires', 'Facture', 'Avoir', 'Provision')");
        $this->addSql("CREATE TYPE mode_reglement_enum AS ENUM ('Virement', 'Chèque', 'Espèces', 'Carte bancaire', 'Prélèvement')");
        $this->addSql("CREATE TYPE statut_echeance_enum AS ENUM ('À venir', 'En cours', 'Terminée', 'Annulée')");
        $this->addSql("CREATE TYPE type_echeance_enum AS ENUM ('Audience', 'Délai de recours', 'Délai de conclusions', 'Dépôt de pièces', 'Expertise', 'Autre')");
        $this->addSql("CREATE TYPE type_evenement_enum AS ENUM ('note', 'appel', 'email', 'courrier', 'audience', 'reunion', 'acte', 'decision', 'document', 'facturation', 'autre')");
        $this->addSql("CREATE TYPE type_partie_adverse_enum AS ENUM ('Personne physique', 'Personne morale')");
        $this->addSql("CREATE TYPE type_convention_honoraire_enum AS ENUM ('fixe', 'horaire', 'pourcentage', 'mixte')");
        $this->addSql("CREATE TYPE statut_rdv_enum AS ENUM ('Planifié', 'Confirmé', 'Annulé', 'Effectué')");
        $this->addSql("CREATE TYPE type_rdv_enum AS ENUM ('Consultation', 'Audience', 'Réunion', 'Appel', 'Expertise', 'Autre')");
        $this->addSql("CREATE TYPE type_ecriture_enum AS ENUM ('Recette', 'Dépense')");

        // ============================================================
        // TABLES
        // ============================================================

        // Cabinet config (singleton id=1)
        $this->addSql("
            CREATE TABLE cabinet_config (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                nom_cabinet VARCHAR(255) NOT NULL DEFAULT 'Mon Cabinet',
                adresse TEXT,
                ville VARCHAR(100),
                code_postal VARCHAR(10),
                telephone VARCHAR(30),
                email VARCHAR(255),
                site_web VARCHAR(255),
                siret VARCHAR(20),
                tva_intracommunautaire VARCHAR(30),
                barreau VARCHAR(100),
                toque VARCHAR(50),
                ri_banque VARCHAR(100),
                ri_iban VARCHAR(50),
                ri_bic VARCHAR(20),
                couleur_primaire VARCHAR(7) DEFAULT '#1a2942',
                logo_path VARCHAR(500),
                mentions_legales TEXT,
                couleurs_matiere JSONB DEFAULT '{}',
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Clients
        $this->addSql("
            CREATE TABLE clients (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                code_client VARCHAR(20) UNIQUE,
                type_client type_client_enum NOT NULL DEFAULT 'Particulier',
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100),
                email VARCHAR(255),
                telephone VARCHAR(30),
                telephone_secondaire VARCHAR(30),
                adresse TEXT,
                ville VARCHAR(100),
                code_postal VARCHAR(10),
                intitule VARCHAR(255),
                siret VARCHAR(20),
                type_societe VARCHAR(100),
                code_ape VARCHAR(10),
                cph VARCHAR(100),
                notes TEXT,
                date_naissance DATE,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Dossiers
        $this->addSql("
            CREATE TABLE dossiers (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                client_id BIGINT NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
                titre VARCHAR(255) NOT NULL,
                couleur VARCHAR(7) DEFAULT '#3B82F6',
                statut statut_dossier_enum NOT NULL DEFAULT 'En cours',
                matiere matiere_enum,
                notes TEXT,
                date_ouverture DATE DEFAULT CURRENT_DATE,
                date_cloture DATE,
                partie_adverse_nom VARCHAR(255),
                partie_adverse_prenom VARCHAR(100),
                partie_adverse_type type_partie_adverse_enum DEFAULT 'Personne physique',
                partie_adverse_entreprise VARCHAR(255),
                convention_type_honoraire type_convention_honoraire_enum,
                convention_montant_fixe NUMERIC(12,2),
                convention_pourcentage_resultat NUMERIC(5,2),
                convention_sous_branche VARCHAR(255),
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Echeances
        $this->addSql("
            CREATE TABLE echeances (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                dossier_id BIGINT NOT NULL REFERENCES dossiers(id) ON DELETE CASCADE,
                titre VARCHAR(255) NOT NULL,
                type_echeance type_echeance_enum NOT NULL DEFAULT 'Autre',
                statut statut_echeance_enum NOT NULL DEFAULT 'À venir',
                date_echeance TIMESTAMP NOT NULL,
                description TEXT,
                rappel_avant INTEGER DEFAULT 3,
                juridiction VARCHAR(255),
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Chronologie (événements de dossier)
        $this->addSql("
            CREATE TABLE chronologie (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                dossier_id BIGINT NOT NULL REFERENCES dossiers(id) ON DELETE CASCADE,
                type_evenement type_evenement_enum NOT NULL DEFAULT 'note',
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                date_evenement TIMESTAMP DEFAULT NOW(),
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Parties adverses supplémentaires
        $this->addSql("
            CREATE TABLE parties_adverses (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                dossier_id BIGINT NOT NULL REFERENCES dossiers(id) ON DELETE CASCADE,
                type type_partie_adverse_enum NOT NULL DEFAULT 'Personne physique',
                nom VARCHAR(255) NOT NULL,
                prenom VARCHAR(100),
                denomination VARCHAR(255),
                avocat VARCHAR(255),
                barreau VARCHAR(100),
                email VARCHAR(255),
                telephone VARCHAR(30),
                adresse TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Factures
        $this->addSql("
            CREATE TABLE factures (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                client_id BIGINT NOT NULL REFERENCES clients(id) ON DELETE RESTRICT,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE SET NULL,
                numero VARCHAR(30) UNIQUE,
                type_document type_document_enum NOT NULL DEFAULT 'Note d''honoraires',
                statut statut_facture_enum NOT NULL DEFAULT 'En cours',
                date_emission DATE NOT NULL DEFAULT CURRENT_DATE,
                montant_ht NUMERIC(12,2) NOT NULL DEFAULT 0,
                taux_tva NUMERIC(5,2) NOT NULL DEFAULT 20,
                montant_ttc NUMERIC(12,2) GENERATED ALWAYS AS (montant_ht * (1 + taux_tva / 100)) STORED,
                provision NUMERIC(12,2) DEFAULT 0,
                mode_reglement mode_reglement_enum NOT NULL DEFAULT 'Virement',
                notes TEXT,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Lignes de facturation
        $this->addSql("
            CREATE TABLE lignes_facture (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                facture_id BIGINT NOT NULL REFERENCES factures(id) ON DELETE CASCADE,
                description VARCHAR(500) NOT NULL,
                quantite NUMERIC(10,2) NOT NULL DEFAULT 1,
                prix_unitaire NUMERIC(12,2) NOT NULL DEFAULT 0,
                unite VARCHAR(50) DEFAULT 'forfait',
                montant_total NUMERIC(12,2) GENERATED ALWAYS AS (quantite * prix_unitaire) STORED,
                ordre INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Paiements
        $this->addSql("
            CREATE TABLE paiements (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                facture_id BIGINT NOT NULL REFERENCES factures(id) ON DELETE CASCADE,
                date_paiement DATE NOT NULL,
                montant NUMERIC(12,2) NOT NULL,
                mode_reglement mode_reglement_enum DEFAULT 'Virement',
                reference VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Rendez-vous
        $this->addSql("
            CREATE TABLE rendezvous (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                client_id BIGINT REFERENCES clients(id) ON DELETE SET NULL,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE SET NULL,
                titre VARCHAR(255) NOT NULL,
                type_rdv type_rdv_enum NOT NULL DEFAULT 'Consultation',
                statut statut_rdv_enum NOT NULL DEFAULT 'Planifié',
                date_debut TIMESTAMP NOT NULL,
                date_fin TIMESTAMP NOT NULL,
                lieu VARCHAR(255),
                description TEXT,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Documents
        $this->addSql("
            CREATE TABLE documents (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE CASCADE,
                client_id BIGINT REFERENCES clients(id) ON DELETE CASCADE,
                nom_original VARCHAR(500) NOT NULL,
                nom_stockage VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100),
                taille BIGINT,
                chemin VARCHAR(1000),
                description TEXT,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Temps (sessions de travail)
        $this->addSql("
            CREATE TABLE temps_sessions (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE SET NULL,
                client_id BIGINT REFERENCES clients(id) ON DELETE SET NULL,
                description VARCHAR(500),
                date_debut TIMESTAMP NOT NULL,
                date_fin TIMESTAMP,
                duree_secondes INTEGER,
                taux_horaire NUMERIC(8,2),
                montant NUMERIC(10,2),
                facture BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Modèles d'actes
        $this->addSql("
            CREATE TABLE modeles_actes (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                nom VARCHAR(255) NOT NULL,
                categorie VARCHAR(100),
                contenu TEXT NOT NULL DEFAULT '',
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Actes générés
        $this->addSql("
            CREATE TABLE actes_generes (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                modele_id BIGINT REFERENCES modeles_actes(id) ON DELETE SET NULL,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE SET NULL,
                nom VARCHAR(255) NOT NULL,
                contenu TEXT NOT NULL DEFAULT '',
                date_generation TIMESTAMP DEFAULT NOW(),
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Écritures comptables (BNC)
        $this->addSql("
            CREATE TABLE ecritures_comptables (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                facture_id BIGINT REFERENCES factures(id) ON DELETE SET NULL,
                type_ecriture type_ecriture_enum NOT NULL DEFAULT 'Recette',
                date_ecriture DATE NOT NULL,
                libelle VARCHAR(500) NOT NULL,
                montant NUMERIC(12,2) NOT NULL,
                tva NUMERIC(12,2) DEFAULT 0,
                categorie VARCHAR(100),
                compte VARCHAR(50),
                reference VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // Vérifications de conflits
        $this->addSql("
            CREATE TABLE verifications_conflits (
                id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                client_id BIGINT REFERENCES clients(id) ON DELETE SET NULL,
                dossier_id BIGINT REFERENCES dossiers(id) ON DELETE SET NULL,
                nom_verifie VARCHAR(255) NOT NULL,
                contexte VARCHAR(500),
                notes TEXT,
                a_conflits BOOLEAN DEFAULT FALSE,
                nombre_conflits INTEGER DEFAULT 0,
                conflits_detail JSONB DEFAULT '[]',
                date_verification TIMESTAMP DEFAULT NOW(),
                created_at TIMESTAMP DEFAULT NOW()
            )
        ");

        // ============================================================
        // INDEX
        // ============================================================

        $this->addSql('CREATE INDEX idx_clients_nom ON clients (nom)');
        $this->addSql('CREATE INDEX idx_clients_email ON clients (email)');
        $this->addSql('CREATE INDEX idx_clients_code ON clients (code_client)');
        $this->addSql('CREATE INDEX idx_dossiers_client ON dossiers (client_id)');
        $this->addSql('CREATE INDEX idx_dossiers_statut ON dossiers (statut)');
        $this->addSql('CREATE INDEX idx_echeances_dossier ON echeances (dossier_id)');
        $this->addSql('CREATE INDEX idx_echeances_date ON echeances (date_echeance)');
        $this->addSql('CREATE INDEX idx_echeances_statut ON echeances (statut)');
        $this->addSql('CREATE INDEX idx_factures_client ON factures (client_id)');
        $this->addSql('CREATE INDEX idx_factures_statut ON factures (statut)');
        $this->addSql('CREATE INDEX idx_factures_date ON factures (date_emission)');
        $this->addSql('CREATE INDEX idx_chronologie_dossier ON chronologie (dossier_id)');
        $this->addSql('CREATE INDEX idx_rdv_date ON rendezvous (date_debut)');
        $this->addSql('CREATE INDEX idx_rdv_client ON rendezvous (client_id)');
        $this->addSql('CREATE INDEX idx_temps_dossier ON temps_sessions (dossier_id)');
        $this->addSql('CREATE INDEX idx_ecritures_date ON ecritures_comptables (date_ecriture)');
        $this->addSql('CREATE INDEX idx_ecritures_type ON ecritures_comptables (type_ecriture)');

        // ============================================================
        // DONNÉES INITIALES
        // ============================================================

        // Cabinet config singleton
        $this->addSql("
            INSERT INTO cabinet_config (nom_cabinet, couleur_primaire)
            VALUES ('Mon Cabinet d''Avocat', '#1a2942')
        ");
    }

    public function down(Schema $schema): void
    {
        // Supprimer dans l'ordre inverse des dépendances
        $this->addSql('DROP TABLE IF EXISTS verifications_conflits CASCADE');
        $this->addSql('DROP TABLE IF EXISTS ecritures_comptables CASCADE');
        $this->addSql('DROP TABLE IF EXISTS actes_generes CASCADE');
        $this->addSql('DROP TABLE IF EXISTS modeles_actes CASCADE');
        $this->addSql('DROP TABLE IF EXISTS temps_sessions CASCADE');
        $this->addSql('DROP TABLE IF EXISTS documents CASCADE');
        $this->addSql('DROP TABLE IF EXISTS rendezvous CASCADE');
        $this->addSql('DROP TABLE IF EXISTS paiements CASCADE');
        $this->addSql('DROP TABLE IF EXISTS lignes_facture CASCADE');
        $this->addSql('DROP TABLE IF EXISTS factures CASCADE');
        $this->addSql('DROP TABLE IF EXISTS parties_adverses CASCADE');
        $this->addSql('DROP TABLE IF EXISTS chronologie CASCADE');
        $this->addSql('DROP TABLE IF EXISTS echeances CASCADE');
        $this->addSql('DROP TABLE IF EXISTS dossiers CASCADE');
        $this->addSql('DROP TABLE IF EXISTS clients CASCADE');
        $this->addSql('DROP TABLE IF EXISTS cabinet_config CASCADE');

        // Supprimer les types énumérés
        $this->addSql('DROP TYPE IF EXISTS type_ecriture_enum');
        $this->addSql('DROP TYPE IF EXISTS statut_rdv_enum');
        $this->addSql('DROP TYPE IF EXISTS type_rdv_enum');
        $this->addSql('DROP TYPE IF EXISTS type_convention_honoraire_enum');
        $this->addSql('DROP TYPE IF EXISTS type_partie_adverse_enum');
        $this->addSql('DROP TYPE IF EXISTS type_evenement_enum');
        $this->addSql('DROP TYPE IF EXISTS type_echeance_enum');
        $this->addSql('DROP TYPE IF EXISTS statut_echeance_enum');
        $this->addSql('DROP TYPE IF EXISTS mode_reglement_enum');
        $this->addSql('DROP TYPE IF EXISTS type_document_enum');
        $this->addSql('DROP TYPE IF EXISTS statut_facture_enum');
        $this->addSql('DROP TYPE IF EXISTS matiere_enum');
        $this->addSql('DROP TYPE IF EXISTS statut_dossier_enum');
        $this->addSql('DROP TYPE IF EXISTS type_client_enum');
    }
}
