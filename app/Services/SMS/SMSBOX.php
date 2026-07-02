<?php

namespace App\Services\SMS;

class SMSBOX
{
    protected $apiUri;
    protected $hlrUri;
    protected $config;


    // CODING

	// default
	// 160 caractères autorisés par SMS, 153 caractères lors d'une concaténation
	// en UDH 6 octets (ou 152 caractères si UDH 7 octets).
	// Attention: certains caractères comptent doubles : [ \ ] ^ { | } ~ €
	public const CODING_DEFAULT = "default";

	// Unicode 16-bits. Permet l'envoi de caractères hors norme GSM.
	// 70 caractères autorisés par SMS, 67 caractères lors d'une concaténation en
	// UDH 6 octets (ou 66 caractères si UDH 7 octets).
	public const CODING_UNICODE = "unicode";

	// Si l'ensemble des caractères soumis sont présents dans la table de
	// l'Alphabet GSM 7-bits, utilise la valeur « default ». Si au moins un caractère
	// ne peut-être représenté dans cet alphabet, utilise la valeur « unicode ».
	public const CODING_AUTO = "auto";


    // CHARSET

	// default
	public const CHARSET_ISO_8859_1 = "iso-8859-1";
	public const CHARSET_ISO_8859_15 = "iso-8859-15";
	public const CHARSET_UTF_8 = "utf-8";


    // UDH

	// Par défaut, lorsque votre message dépasse le nombre de caractères autorisés (160 caractères en alphabet GSM ou
	// 70 caractères en Unicode), les SMS réceptionnés sont automatiquement concaténés en un seul et unique affichage sur le
	// mobile de destination.

	// Par défaut. Utilisation d'un UDH (entête) de 6 octets (255 références possibles).
	public const UDH_6_BYTES = 1;
	// Utilisation d'un UDH (entête) de 7 octets à double référence (255² références possibles).
	public const UDH_7_BYTES = 2;
	// Désactive la concaténation. Chaque partie sera réceptionnée de façon distincte par le destinataire.
	public const UDH_NONE = 0;


    // MODE

	// default
	// Définie l'utilisation du mode d'envoi Standard.
	// Ce mode affiche un code numérique (ShortCode) à 3, 4 ou 5 chiffres
	// (Exemple: 20193) en tant qu'émetteur du message sur le mobile du destinataire.
	public const MODE_STANDARD = "Standard";

	// Définie l'utilisation du mode d'envoi Expert.
	// Ce mode vous permet de vous identifier en tant qu'émetteur du message
	// (Nom qui s'affiche sur le mobile du destinataire avant la lecture du message).
	public const MODE_EXPERT = "Expert";

	// Définie l'utilisation du mode d'envoi Réponse.
	// Ce mode vous permet d'envoyer vos messages en donnant la possibilité à
	// vos destinataires de vous répondre (par SMS) depuis leur mobile.
	// Les éventuelles réponses sont directement stockées dans votre boite de
	// réception disponible depuis votre Espace Client ou acheminées vers votre
	// applicatif (routage vers l'URL de votre choix, par Callback).
	// Durée de validité : 48 heures suivant l'envoi du message, réponse à la
	// charge du destinataire au prix d'un SMS vers la France Métropolitaine (hors surcoût éventuel).
	public const MODE_RESPONSE = "Reponse";


    // STRATEGY

	// Communication interpersonnelle privée (entre deux personnes physiques)

	// Cette stratégie intègre uniquement les communications privées entre deux
	// personnes physiques. L'envoi de messages à caractère commercial par le
	// biais de cette stratégie n'est pas autorisé.
	// La présence d'un numéro en liste noire globale ou privative n'est pas vérifiée.
	// 50 destinataires maximum par envoi.
	public const STRATEGY_PRIVATE = 1;

	// Message d'alerte / Notification proactive / Livraison de service ou de contenu

	// Cette stratégie intègre uniquement les alertes, notifications proactives, ainsi
	// que la livraison de services ou de contenus. L'envoi de messages à
	// caractère commercial par le biais de cette stratégie n'est pas autorisé.
	// La présence d'un numéro en liste noire globale ou privative n'est pas vérifiée.
	// 50 destinataires maximum par envoi.
	public const STRATEGY_ALERT = 2;

	// Communication de groupe sans caractère commercial

	// Cette stratégie intègre les communications sans caractère commercial vers
	// un groupe fermé d'utilisateurs. L'envoi de messages à caractère commercial
	// par le biais de cette stratégie n'est pas autorisé.
	// La présence d'un numéro en liste noire globale n'est pas vérifiée. Cependant,
	// la présence d'un numéro en liste noire privative est vérifiée.
	public const STRATEGY_GROUP = 3;

	// Communication à caractère commercial

	// Cette stratégie intègre les communications à caractère
	// commercial/marketing.
	// Pour rappel, les messages marketing regroupent toute offre ou promotion d'un
	// service, même gratuit.
	// La présence d'un numéro en liste noire globale ou privative est vérifiée.
	// Vous devez fournir une solution de désinscription aux destinataires à
	// l'intérieur de votre message.
	// L'envoi de messages marketing est interdit le soir après 21h00, le matin avant
	// 8h00 ainsi que le dimanche et les jours fériés.
	public const STRATEGY_COMMERCIAL = 4;


    // TYPE

	// default
	// Envoi « Classique ». Le SMS est enregistré dans la mémoire (Carte SIM ou mobile) du téléphone.
	public const TYPE_CLASSIC = 0;

	// Envoi « Flash ». Le SMS est directement affiché à l'écran du mobile et n'est pas
	// enregistré dans la mémoire (Carte SIM ou mobile) du téléphone.
	public const TYPE_FLASH = 1;

    /**
     * 
     */
    protected function setup()
    {
    	$this->config = [];

    	// SMSBOX API

    	// SMS BOX API URI
    	$this->apiUri = config('smsbox.api_uri');
    	$this->hlrUri = config('smsbox.hlr_uri');

    	// SMS BOX API KEY
    	$this->config['apikey'] = config('smsbox.api_key');
    	
    	// Permettre à l'utilisateur de répondre à l'SMS
    	$this->config['mode'] = self::MODE_RESPONSE;
    	$this->config['strategy'] = self::STRATEGY_PRIVATE;
    	$this->config['charset'] = self::CHARSET_UTF_8;
    	$this->config['id'] = 1;
    	$this->config['callback'] = 1;
    }

    /**
     * Par défaut l'Alphabet GSM 7-bits est utilisé.
     * 
	 * Valeurs autorisées :
	 * SMSBOX::CHARSET_ISO_8859_1 (valeur par défaut)
	 * SMSBOX::CHARSET_ISO_8859_15
	 * SMSBOX::CHARSET_UTF_8
     */
    public function setCoding($coding)
	{
    	if (
    		!in_array($coding, [
    			self::CODING_DEFAULT, 
    			self::CODING_UNICODE, 
    			self::CHARSET_UTF_8
    		])
    	) {
    		throw new \Exception(trans('sms.smsbox.error.invalid_encoding'));
    	}

    	$this->config['coding'] = $coding;
    }

    /**
     * Vous pouvez spécifier le charset utilisé dans le contenu des paramètres de votre requête.
     * 
	 * Valeurs autorisées :
	 * SMSBOX::CHARSET_ISO_8859_1 (valeur par défaut)
	 * SMSBOX::CHARSET_ISO_8859_15
	 * SMSBOX::CHARSET_UTF_8
     */
    public function setCharset($charset)
	{
    	if (
    		!in_array($charset, [
    			self::CHARSET_ISO_8859_1, 
    			self::CHARSET_ISO_8859_15, 
    			self::CODING_AUTO
    		])
    	) {
    		throw new \Exception(trans('sms.smsbox.error.invalid_charset'));
    	}

    	$this->config['charset'] = $charset;
    }

    /**
     * Definir le mode d'envoi
     * 
	 * Valeurs autorisées :
	 * SMSBOX::MODE_STANDARD (valeur par défaut)
	 * SMSBOX::MODE_EXPERT
	 * SMSBOX::MODE_RESPONSE
     */
    public function setMode($mode)
	{
    	if (
    		!in_array($mode, [
    			self::MODE_STANDARD, 
    			self::MODE_EXPERT, 
    			self::MODE_RESPONSE
    		])
    	) {
    		throw new \Exception(trans('sms.smsbox.error.invalid_mode'));
    	}

    	$this->config['mode'] = $mode;
    }

    /**
     * Definir la strategie
     * 
	 * Valeurs autorisées :
	 * SMSBOX::STRATEGY_PRIVATE (valeur par défaut)
	 * SMSBOX::STRATEGY_ALERT
	 * SMSBOX::STRATEGY_GROUP
	 * SMSBOX::STRATEGY_COMMERCIAL
     */
    public function setStrategy($strategy)
	{
    	if (
			!in_array($strategy, [
				self::STRATEGY_PRIVATE, 
				self::STRATEGY_ALERT, 
				self::STRATEGY_GROUP, 
				self::STRATEGY_COMMERCIAL
			])
		) {
    		throw new \Exception(trans('sms.smsbox.error.invalid_strategy'));
    	}

    	$this->config['strategy'] = $strategy;
    }

    /**
     * Definir le type de sms à envoyer
     * 
	 * Valeurs autorisées :
	 * SMSBOX::TYPE_CLASSIC (valeur par défaut)
	 * SMSBOX::TYPE_FLASH
     */
    public function setType($type)
	{
    	if (
			!in_array($type, [
				self::TYPE_CLASSIC, 
				self::TYPE_FLASH
			])
		) {
    		throw new \Exception(trans('sms.smsbox.error.invalid_type'));
    	}

    	$this->config['type'] = $type;
    }

    /**
     * Definir l'origine
     * 
	 * @param origine
	 *        Le nom ou le numéro de l'émetteur du SMS 
	 *        (Maximum 15 chiffres ou 11 caractères). 
	 *        Si laissé vide, utilisation de votre émetteur par défaut.
	 *
     */
    public function setOrigine($origine)
	{
    	if (
			!isset($this->config['mode']) ||
			$this->config['mode'] != self::MODE_EXPERT
		) {
    		throw new \Exception(trans('sms.smsbox.error.origin'));
    	}

    	$this->config['origine'] = $origine;
    }

    /**
     * Definir si on devrait récupérer l'ID SMSBOX
	 * du message envoyé
	 *
	 * @param {boolean} $retrieveId
	 *        Récupérer l'ID venant de SMSBOX du SMS employé
     */
    public function setRetrieveId($retrieveId)
	{
    	$this->config['id'] = $retrieveId ? 1 : 0;
    }

    /**
     * Definir si on devrait récevoir un accusé de réception
	 * du message envoyé
	 *
	 * @param {boolean} $callback
	 *        
     */
    public function setCallback($callback)
	{
    	$this->config['callback'] = $callback ? 1 : 0;
    }

    /**
     * Envoyer un message
     * 
	 * @param dest
	 *        numéro mobile du callback
	 *
	 * @param message
	 *        message à envoyer
     */
    public function sendSMS($dest, $message)
	{
        $this->setup();

    	// Vérifier que l'API est bien configuré
    	if (empty($this->apiUri) || empty($this->config['apikey'])) {
    		throw new \Exception(trans('sms.smsbox.error.empty_setting'));
    	}

    	// Vérifier que le message n'est pas vide
    	if (empty($message)) {
            throw new \Exception(trans('sms.smsbox.error.empty_message'));
    	}

    	// Vérifier que le destinataire est bien renseigné
    	if (empty($dest)) {
            throw new \Exception(trans('sms.smsbox.error.empty_number'));
    	}

    	if (is_array($dest)) {
    		$dest = implode(',', $dest);
    	}

    	$data = $this->config;
    	$data['msg'] = $message;
    	$data['dest'] = $dest;

    	// URL
        $url = $this->apiUri . "?" . http_build_query($data);


        // Send CURL TO SMS BOX API

        // send CURL to API URI with params
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);


        // CHECK RESPONSE

        if (substr($output, 0, 2) == "OK") {
        	if (isset($this->config['id'])) {
        		return substr($output, 3);
        	} else {
        		return 0;
        	}
        } else {
	        switch ($output) {
	        	case 'OK':
	        		return true;
	        	
	        	case 'ERROR 01':
					throw new \Exception(trans('sms.smsbox.error.01'));
	        	
	        	case 'ERROR 02':
					throw new \Exception(trans('sms.smsbox.error.02'));
	        	
	        	case 'ERROR 03':
					throw new \Exception(trans('sms.smsbox.error.03'));
	        	
	        	case 'ERROR 04':
					throw new \Exception(trans('sms.smsbox.error.04'));
	        	
	        	case 'ERROR 05':
					throw new \Exception(trans('sms.smsbox.error.05'));

	        	default:
					throw new \Exception($output . " : " . trans('sms.smsbox.error.other'));
	        }
        }
    }

    public function hlr($mobile_phone_numbers)
	{
        $this->setup();

    	if (!is_array($mobile_phone_numbers)) {
    		$mobile_phone_numbers = [$mobile_phone_numbers];
    	}

    	if (empty($mobile_phone_numbers)) {
    		throw new \Exception(trans('sms.smsbox.error.empty_number'));
    	}

    	if (count($mobile_phone_numbers) > 100) {
    		throw new \Exception(trans('sms.smsbox.error.max_numbers'));
    	}


    	$data = [
    		'apikey' => $this->config['apikey'],
    		'recipients' => implode(',', $mobile_phone_numbers)
    	];

    	// URL
        $url = $this->hlrUri . "?" . http_build_query($data);


        // Send CURL TO SMS BOX API

        // send CURL to API URI with params
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}