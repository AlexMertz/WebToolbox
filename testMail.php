<?php

 $to = 'sylvain.doignon@gmail.com';

/* Construction du message */
$msg  = 'Bonjour,'."\r\n\r\n";
$msg .= 'Ce mail a été envoyé depuis monsite.com par';
$msg .= 'Voici le message qui vous est adressé :'."\r\n";
$msg .= '***************************'."\r\n";
$msg .= '***************************'."\r\n";

/* En-têtes de l'e-mail */
$headers = 'From: '. "onveutdirable@utc.fr" .' <On Veut Durable>'."\r\n\r\n";

/* Envoi de l'e-mail */
if (mail($to, "Suejt", $msg, $headers))
{
echo'E-mail envoyé avec succès';

}
else
{
echo'Erreur d\'envoi de l\'e-mail';
}
