<?php
/**
 * Profils commerciaux FRIPE / BROC — SIRET, mentions légales
 *
 * Données de démonstration — remplacer par les vrais SIRET / KBIS avant mise en prod.
 *
 * Catégories FRIPE : Femmes, Hommes, Enfants, Accessoires
 * Catégories BROC  : tout le reste (Brocante, Décoration, Art de la Table, Jouets, Livres, etc.)
 */

class EntityProfile
{
    public const SIRET_FRIPE = '000 000 000 00011';
    public const SIRET_BROC = '000 000 000 00022';

    public const NOM_FRIPE = "FRIPERIE CASHFLOW";
    public const NOM_BROC = "BROCANTE CASHFLOW";

    public const MENTION_TVA = "Articles d'occasion — TVA non applicable, art. 293 B du CGI";
    public const MENTION_RETOUR   = "Aucun remboursement ni échange possible. Passé la remise en mains propres, les articles sont vendus en l'état.";
    public const MENTION_ECO      = "Conformément à l'article L541-10-1 du code de l'environnement, le prix de revente des articles d'occasion est affiché en magasin.";

    public const MAGASIN_NOM = 'CashFlow';
    public const MAGASIN_EMAIL = 'contact@example.com';
    public const MAGASIN_ADR      = 'Adresse du magasin, 00000 Ville';

    /** Catégories relevant du profil FRIPE (Fripe) */
    public static function fripeCategories(): array
    {
        return ['Femmes', 'Hommes', 'Enfants', 'Accessoires'];
    }
}
