<?php


namespace App\Utils;


use App\Entity\EvaluationsTypes;
use Symfony\Component\Filesystem\Filesystem;

class Compile
{
    /**
     * @param EvaluationsTypes $evaluationsTypes
     * @param string $code
     * @param string|null $replace
     * @param array $testedKeys
     * @desc Le but de la fonction est de pouvoir compiler et de renvoyer le résultat de la compilation. La fonction doit fonctionner pour la correction (Avec tout le code opérationnel) et le code utilisateur, remplacer le {ici} par ce que la personne à insérer.
     * @return array [0=>HasSucceed,0=>Message]
     */
    public static function compile(EvaluationsTypes $evaluationsTypes, string $code, array $testedKeys, string $replace=null) {
        //Le fichier source aura comme intitulé tmp_"timestamp".cpp par exemple
        $sourceName = "tmp_".date('d_m_Y').".".$evaluationsTypes->getLanguage();
        $filesystem = new Filesystem();

        //Check if tmp code exit
        if(!$filesystem->exists(dirname(__DIR__).'/../var/compile')) {
            $filesystem->mkdir(dirname(__DIR__).'/../var/compile', 0700);
        }


        if(PHP_OS == "WINNT") {
            $execname = "tmp_".date('d_m_Y').".exe";
        } else {
            //On est sur du linux
            $execname = "tmp_".date('d_m_Y');
        }

        if($replace != null) {
            $code = str_replace('{ici}',$replace,$code);
        }

        //Check si il y a des mots interdits !
        $hasBannedWords=false;
        foreach ($evaluationsTypes->getBannedWords() as $bannedWord) {
            if(preg_match('/'.$bannedWord.'/', $code) == 1) {
                $hasBannedWords=true;
            }
        }

        if($hasBannedWords)
            return [false, "bannedWords"];

        if(!$filesystem->exists(dirname(__DIR__).'/../var/compile/'.$sourceName)) {
            $filesystem->touch(dirname(__DIR__).'/../var/compile/'.$sourceName, 0700);
        }

        file_put_contents(dirname(__DIR__).'/../var/compile/'.$sourceName, $code);

        $compileCode = str_replace('{fichier}',dirname(__DIR__).'/../var/compile/'.$sourceName, $evaluationsTypes->getCommand());
        $compileCode = str_replace('{fichierResponse}',dirname(__DIR__).'/../var/compile/'.$execname, $compileCode);

        $exec = shell_exec($compileCode);

        if(preg_match('/error:/i', $exec) === 1) {
            $exec = preg_replace('/((?:[^\/]*\/)*)/i', "", $exec);
            return [false, $exec];
        }

        $result = [];

        foreach ($testedKeys as $testedKey) {
            //Execution du programme !
            //Il faut différencier les deux cas, celui où il n'y a pas de clé et celui ou il y en a un !
            $cmd = dirname(__DIR__) . '/../var/compile/' . $execname . " " . $testedKey . " 2>&1";
            $exec = shell_exec($cmd);

            if(preg_match('/error:/i', $exec) === 1) {
                $exec = preg_replace('/((?:[^\/]*\/)*)/i', "", $exec);
                return [false, $exec];
            }

            array_push($result, $exec);
        }

        if(count($testedKeys) == 0) {
            //Pas de jeu d'essaie !
            $cmd = dirname(__DIR__).'/../var/compile/'.$execname." 2>&1";
            $exec = shell_exec($cmd);

            if(preg_match('/error:/i', $exec) === 1) {
                $exec = preg_replace('/((?:[^\/]*\/)*)/i', "", $exec);
                return [false, $exec];
            }

            array_push($result, $exec);
        }

        return $result;
    }
}