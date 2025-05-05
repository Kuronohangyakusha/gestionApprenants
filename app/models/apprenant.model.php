<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
require_once __DIR__ . '/../../vendor/autoload.php';
 
 


function get_all_referentiels(): array {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
   
    $promotion_active = null;
    foreach ($data['promotions'] as $promotion) {
        if ($promotion['statut'] === 'Active') {
            $promotion_active = $promotion;
            break;
        }
    }
    
    
    if (!$promotion_active) {
        return [];
    }
    
    
    $referentiels_ids = $promotion_active['referenciels'] ?? [];
    
  
    $referentiels_actifs = [];
    

    foreach ($data['referenciel'] as $referentiel) {
        if (in_array($referentiel['id'], $referentiels_ids)) {
            $referentiels_actifs[] = $referentiel;
        }
    }
    
    return $referentiels_actifs;
}


function get_statuts_uniques(array $apprenants): array {
    $statuts = [];
    
    foreach ($apprenants as $apprenant) {
        $statut = $apprenant['statut'] ?? 'Non défini';
        if (!in_array($statut, $statuts)) {
            $statuts[] = $statut;
        }
    }
    
    return $statuts;
}

 
function generer_mot_de_passe($longueur = 8): string {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $mot_de_passe = '';
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longueur; $i++) {
        $mot_de_passe .= $caracteres[mt_rand(0, $max)];
    }
    
    return $mot_de_passe;
}


 

function envoyer_identifiants_par_email(array $apprenant, string $mot_de_passe): bool {
   
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        
        error_log("PHPMailer n'est pas installé, tentative d'utilisation de la fonction mail() native");
        return envoyer_email_methode_native($apprenant, $mot_de_passe);
    }

    try {
       
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
         
        $mail->isSMTP();                                      
        $mail->Host       = 'smtp.gmail.com';                
        $mail->SMTPAuth   = true;                              
        $mail->Username   = 'nndeyendiaye85@gmail.com';                
        $mail->Password   = 'xtkp yxtm ftja zslv';                        
        $mail->SMTPSecure = 'tls';                            
        $mail->Port       = 587;                              
        
        
        $mail->setFrom('nndeyendiaye85@gmail.com', 'Sonatel Formation');
        $mail->addAddress($apprenant['email'], $apprenant['prenom'] . ' ' . $apprenant['nom']);
        
        
        $mail->isHTML(true);                                  
        $mail->Subject = 'Vos identifiants de connexion';
        
       
        $htmlContent = "
        <h2>Bienvenue dans notre formation</h2>
        <p>Bonjour {$apprenant['prenom']} {$apprenant['nom']},</p>
        <p>Nous avons le plaisir de vous accueillir dans notre formation.</p>
        <p>Voici vos identifiants de connexion :</p>
        <ul>
            <li><strong>Matricule :</strong> {$apprenant['matricule']}</li>
            <li><strong>Email :</strong> {$apprenant['email']}</li>
            <li><strong>Mot de passe :</strong> {$mot_de_passe}</li>
        </ul>
        <p>Nous vous recommandons de changer votre mot de passe lors de votre première connexion.</p>
        <p>Cordialement,<br>L'équipe pédagogique</p>
        ";
        $mail->Body = $htmlContent;
        
        
        $textContent = "Bonjour {$apprenant['prenom']} {$apprenant['nom']},\n\n";
        $textContent .= "Nous avons le plaisir de vous accueillir dans notre formation.\n\n";
        $textContent .= "Voici vos identifiants de connexion :\n";
        $textContent .= "Matricule : {$apprenant['matricule']}\n";
        $textContent .= "Email : {$apprenant['email']}\n";
        $textContent .= "Mot de passe : {$mot_de_passe}\n\n";
        $textContent .= "Nous vous recommandons de changer votre mot de passe lors de votre première connexion.\n\n";
        $textContent .= "Cordialement,\nL'équipe pédagogique";
        $mail->AltBody = $textContent;
        
        
        $result = $mail->send();
        error_log("Email envoyé avec succès à {$apprenant['email']}");
        return true;
    } catch (Exception $e) {
        error_log("Échec de l'envoi de l'email à {$apprenant['email']} : " . $e->getMessage());
        return false;
    }
}

 
function envoyer_email_methode_native(array $apprenant, string $mot_de_passe): bool {
    $to = $apprenant['email'];
    $subject = 'Vos identifiants de connexion';
    
    $message = "Bonjour " . $apprenant['prenom'] . " " . $apprenant['nom'] . ",\n\n";
    $message .= "Nous avons le plaisir de vous accueillir dans notre formation.\n\n";
    $message .= "Voici vos identifiants de connexion :\n";
    $message .= "Matricule : " . $apprenant['matricule'] . "\n";
    $message .= "Email : " . $apprenant['email'] . "\n";
    $message .= "Mot de passe : " . $mot_de_passe . "\n\n";
    $message .= "Nous vous recommandons de changer votre mot de passe lors de votre première connexion.\n\n";
    $message .= "Cordialement,\n";
    $message .= "L'équipe pédagogique";
    
    
    $headers = 'From: Sonatel Formation <noreply@sonatel.sn>' . "\r\n" .
               'Reply-To: noreply@sonatel.sn' . "\r\n" .
               'X-Mailer: PHP/' . phpversion() . "\r\n" .
               'MIME-Version: 1.0' . "\r\n" .
               'Content-type: text/plain; charset=UTF-8' . "\r\n";
    
   
    $result = mail($to, $subject, $message, $headers);
    
     
    error_log("Envoi d'email à {$to} via mail() native: " . ($result ? 'Succès' : 'Échec'));
    
    return $result;
}
 
function valider_donnees_apprenant(array $donnees, array $referentiels = []): array {
    $erreurs = [];
    
    
    if (empty($donnees['prenom'])) {
        $erreurs['prenom'] = "Le prénom est obligatoire";
    }
    
      
    if (empty($donnees['email'])) {
        $erreurs['email'] = "L'email est obligatoire pour envoyer les identifiants de connexion";
    } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'email n'est pas valide";
    }
    
    
    if (empty($donnees['telephone'])) {
        $erreurs['telephone'] = "Le numéro de téléphone est obligatoire";
    }
    
    
    if (!empty($referentiels) && empty($donnees['referentiel'])) {
        $erreurs['referentiel'] = "Le référentiel est obligatoire";
    }
    
    
    if (empty($donnees['tuteur_nom'])) {
        $erreurs['tuteur_nom'] = "Le nom du tuteur est obligatoire";
    }
    
     
    if (empty($donnees['tuteur_telephone'])) {
        $erreurs['tuteur_telephone'] = "Le téléphone du tuteur est obligatoire";
    }
    
    
    if (!empty($donnees['password']) && strlen($donnees['password']) < 8) {
        $erreurs['password'] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    return $erreurs;
}
 
 

 
function exporter_pdf(array $apprenants): void {
    
    if (!class_exists('FPDF')) {
        require_once __DIR__ . '/../../vendor/setasign/fpdf/fpdf.php';
    }
    
     
    $pdf = new FPDF('L', 'mm', 'A4');  
    $pdf->AddPage();
    
    
    $pdf->SetFont('Arial', 'B', 12);
    
     
    $pdf->Cell(0, 10, 'Liste des Apprenants', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    
    
    $header = ['Matricule', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Référentiel', 'Statut'];
    $w = [25, 30, 30, 50, 30, 40, 25];  
     
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'B', 10);
    
     
    for($i=0; $i<count($header); $i++) {
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    
     
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 10);
    
    
    $fill = false;
    foreach($apprenants as $apprenant) {
        $pdf->Cell($w[0], 6, $apprenant['matricule'], 1, 0, 'L', $fill);
        $pdf->Cell($w[1], 6, $apprenant['prenom'], 1, 0, 'L', $fill);
        $pdf->Cell($w[2], 6, $apprenant['nom'] ?? '-', 1, 0, 'L', $fill);
        $pdf->Cell($w[3], 6, $apprenant['email'], 1, 0, 'L', $fill);
        $pdf->Cell($w[4], 6, $apprenant['telephone'], 1, 0, 'L', $fill);
        $pdf->Cell($w[5], 6, $apprenant['referentiel'] ?? '-', 1, 0, 'L', $fill);
        $pdf->Cell($w[6], 6, $apprenant['statut'] ?? '-', 1, 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;  
    }
    
     
    $pdf->Ln(10);
    $pdf->Cell(0, 6, 'Document généré le ' . date('d/m/Y H:i'), 0, 0, 'R');
    
   
    $pdf->Output('liste_apprenants.pdf', 'D'); 
    exit;
}

 
function exporter_excel(array $apprenants): void {
     
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
         
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="liste_apprenants.csv"');
        
         
        $output = fopen('php://output', 'w');
        
       
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        
        fputcsv($output, ['Matricule', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Date de naissance', 'Lieu de naissance', 'Adresse', 'Référentiel', 'Statut', 'Tuteur', 'Lien de parenté']);
        
        
        foreach ($apprenants as $apprenant) {
            fputcsv($output, [
                $apprenant['matricule'] ?? '',
                $apprenant['prenom'] ?? '',
                $apprenant['nom'] ?? '',
                $apprenant['email'] ?? '',
                $apprenant['telephone'] ?? '',
                $apprenant['date_naissance'] ?? '',
                $apprenant['lieu_naissance'] ?? '',
                $apprenant['adresse'] ?? '',
                $apprenant['referentiel'] ?? '',
                $apprenant['statut'] ?? '',
                $apprenant['tuteur_nom'] ?? '',
                $apprenant['lien_parente'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    } else {
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        
        $styleHeader = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
         
        $headers = [
            'A1' => 'Matricule',
            'B1' => 'Prénom',
            'C1' => 'Nom',
            'D1' => 'Email',
            'E1' => 'Téléphone',
            'F1' => 'Date de naissance',
            'G1' => 'Lieu de naissance',
            'H1' => 'Adresse',
            'I1' => 'Référentiel',
            'J1' => 'Statut',
            'K1' => 'Tuteur',
            'L1' => 'Lien de parenté'
        ];
        
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
         
        $sheet->getStyle('A1:L1')->applyFromArray($styleHeader);
        
        
        $row = 2;
        foreach ($apprenants as $apprenant) {
            $sheet->setCellValue('A' . $row, $apprenant['matricule'] ?? '');
            $sheet->setCellValue('B' . $row, $apprenant['prenom'] ?? '');
            $sheet->setCellValue('C' . $row, $apprenant['nom'] ?? '');
            $sheet->setCellValue('D' . $row, $apprenant['email'] ?? '');
            $sheet->setCellValue('E' . $row, $apprenant['telephone'] ?? '');
            $sheet->setCellValue('F' . $row, $apprenant['date_naissance'] ?? '');
            $sheet->setCellValue('G' . $row, $apprenant['lieu_naissance'] ?? '');
            $sheet->setCellValue('H' . $row, $apprenant['adresse'] ?? '');
            $sheet->setCellValue('I' . $row, $apprenant['referentiel'] ?? '');
            $sheet->setCellValue('J' . $row, $apprenant['statut'] ?? '');
            $sheet->setCellValue('K' . $row, $apprenant['tuteur_nom'] ?? '');
            $sheet->setCellValue('L' . $row, $apprenant['lien_parente'] ?? '');
            $row++;
        }
        
        
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="liste_apprenants.xlsx"');
        header('Cache-Control: max-age=0');
        
        
        $writer->save('php://output');
        exit;
    }
}


function traiter_import_excel(string $file_path): array {
    $resultats = [
        'importes' => 0,
        'erreurs' => 0,
        'doublons' => 0,
        'messages' => []
    ];
    
    // Déterminer le type de fichier
    $file_extension = strtolower(pathinfo($_FILES['fichier_excel']['name'], PATHINFO_EXTENSION));
    
    // Lire les données du fichier
    if ($file_extension === 'csv') {
        $apprenants_data = lire_csv($file_path);
    } else {
        // Utiliser SimpleXLSX ou PhpSpreadsheet si disponible
        if (class_exists('SimpleXLSX')) {
            $apprenants_data = lire_xlsx_simple($file_path);
        } elseif (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            $apprenants_data = lire_xlsx_phpspreadsheet($file_path);
        } else {
            $resultats['messages'][] = "Aucune bibliothèque Excel n'est disponible. Veuillez utiliser le format CSV.";
            return $resultats;
        }
    }
    
    // Si aucune donnée n'a été lue
    if (empty($apprenants_data)) {
        $resultats['messages'][] = "Le fichier semble être vide ou mal formaté";
        return $resultats;
    }
    
    // Récupérer les référentiels disponibles pour la validation
    $referentiels = get_all_referentiels();
    $referentiels_noms = array_column($referentiels, 'nom');
    
    // Traiter chaque ligne du fichier
    foreach ($apprenants_data as $index => $row) {
        // Ignorer la première ligne si elle contient des en-têtes
        if ($index === 0 && (
            strtolower($row[0]) === 'prenom' || 
            strtolower($row[0]) === 'prénom' || 
            strtolower($row[0]) === 'nom'
        )) {
            continue;
        }
        
        // Vérifier si la ligne est vide
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Préparer les données de l'apprenant
        $apprenant_data = [
            'prenom' => $row[0] ?? '',
            'nom' => $row[1] ?? '',
            'date_naissance' => $row[2] ?? '',
            'lieu_naissance' => $row[3] ?? '',
            'adresse' => $row[4] ?? '',
            'email' => $row[5] ?? '',
            'telephone' => $row[6] ?? '',
            'referentiel' => $row[7] ?? '',
            'tuteur_nom' => $row[8] ?? '',
            'lien_parente' => $row[9] ?? '',
            'tuteur_adresse' => $row[10] ?? '',
            'tuteur_telephone' => $row[11] ?? '',
            'photo' => 'assets/images/default_avatar.jpg'  // Image par défaut
        ];
        
        // Valider les données de l'apprenant
        $erreurs = valider_donnees_apprenant($apprenant_data, $referentiels);
        
        // Si des erreurs sont détectées, les enregistrer et passer à la ligne suivante
        if (!empty($erreurs)) {
            $ligne = $index + 1; // +1 pour correspondre à la numérotation dans Excel
            $resultats['erreurs']++;
            $erreurs_str = implode(', ', $erreurs);
            $resultats['messages'][] = "Ligne $ligne: $erreurs_str";
            continue;
        }
        
        // Vérifier si le référentiel existe
        if (!empty($apprenant_data['referentiel']) && !in_array($apprenant_data['referentiel'], $referentiels_noms)) {
            $ligne = $index + 1;
            $resultats['erreurs']++;
            $resultats['messages'][] = "Ligne $ligne: Le référentiel '{$apprenant_data['referentiel']}' n'existe pas";
            continue;
        }
        
        // Vérifier si l'email existe déjà
        if (!empty($apprenant_data['email']) && email_existe_deja($apprenant_data['email'])) {
            $ligne = $index + 1;
            $resultats['doublons']++;
            $resultats['messages'][] = "Ligne $ligne: Un apprenant avec l'email '{$apprenant_data['email']}' existe déjà";
            continue;
        }
        
        // Ajouter l'apprenant
        $succes = ajouter_apprenant($apprenant_data);
        
        if ($succes) {
            $resultats['importes']++;
        } else {
            $ligne = $index + 1;
            $resultats['erreurs']++;
            $resultats['messages'][] = "Ligne $ligne: Erreur lors de l'ajout dans la base de données";
        }
    }
    
    return $resultats;
}


 
function lire_csv(string $file_path): array {
    $data = [];
    if (($handle = fopen($file_path, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

/**
 * Lit un fichier XLSX avec SimpleXLSX et retourne les données
 */
function lire_xlsx_simple(string $file_path): array {
    $data = [];
    if ($xlsx = SimpleXLSX::parse($file_path)) {
        $data = $xlsx->rows();
    }
    return $data;
}

/**
 * Lit un fichier XLSX avec PhpSpreadsheet et retourne les données
 */
function lire_xlsx_phpspreadsheet(string $file_path): array {
    $data = [];
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
    } catch (\Exception $e) {
        error_log("Erreur lors de la lecture du fichier Excel: " . $e->getMessage());
    }
    return $data;
}
 


/**
 * Récupère un apprenant par son ID
 * 
 * @param int $id ID de l'apprenant
 * @return array|null Les données de l'apprenant ou null si non trouvé
 */
 

/**
 * Récupère les promotions associées à un apprenant
 * 
 * @param int $apprenant_id ID de l'apprenant
 * @return array Liste des promotions associées à l'apprenant
 */
function get_promotions_by_apprenant($apprenant_id) {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    $promotions_apprenant = [];
    
    // Vérifier les inscriptions de l'apprenant aux promotions
    foreach ($data['inscriptions'] ?? [] as $inscription) {
        if ($inscription['apprenant_id'] == $apprenant_id) {
            // Trouver les détails de la promotion
            foreach ($data['promotions'] ?? [] as $promotion) {
                if ($promotion['id'] == $inscription['promotion_id']) {
                    $promotions_apprenant[] = $promotion;
                    break;
                }
            }
        }
    }
    
    return $promotions_apprenant;
}

/**
 * Récupérer les compétences d'un apprenant
 * 
 * @param int $apprenant_id ID de l'apprenant
 * @return array Liste des compétences et leur niveau pour l'apprenant
 */
function get_competences_apprenant($apprenant_id) {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    $competences = [];
    
    foreach ($data['evaluations'] ?? [] as $evaluation) {
        if ($evaluation['apprenant_id'] == $apprenant_id) {
            // Trouver les détails de la compétence
            foreach ($data['competences'] ?? [] as $competence) {
                if ($competence['id'] == $evaluation['competence_id']) {
                    $competences[] = [
                        'nom' => $competence['nom'],
                        'description' => $competence['description'],
                        'niveau' => $evaluation['niveau']
                    ];
                    break;
                }
            }
        }
    }
    
    return $competences;
}
 

if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    echo "PhpSpreadsheet n'est pas installé. Installation recommandée: composer require phpoffice/phpspreadsheet";
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Créer un nouveau spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Définir les en-têtes
$headers = [
    'A1' => 'Prénom',
    'B1' => 'Nom',
    'C1' => 'Date de naissance',
    'D1' => 'Lieu de naissance',
    'E1' => 'Adresse',
    'F1' => 'Email',
    'G1' => 'Téléphone',
    'H1' => 'Référentiel',
    'I1' => 'Nom du tuteur',
    'J1' => 'Lien de parenté',
    'K1' => 'Adresse du tuteur',
    'L1' => 'Téléphone du tuteur'
];

// Appliquer le style aux en-têtes
$styleHeader = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4472C4'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];

// Ajouter les en-têtes
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Appliquer le style aux en-têtes
$sheet->getStyle('A1:L1')->applyFromArray($styleHeader);

// Ajouter des exemples de données
$exampleData = [
    ['Jean', 'Dupont', '1998-05-15', 'Paris', '123 Rue Exemple, Ville', 'jean.dupont@example.com', '06 12 34 56 78', 'Développement Web', 'Marie Dupont', 'Mère', '123 Rue Exemple, Ville', '06 98 76 54 32'],
    ['Fatou', 'Ndiaye', '2000-09-20', 'Dakar', '456, Avenue Exemple, Dakar', 'fatou.ndiaye@example.com', '77 123 45 67', 'Marketing Digital', 'Omar Ndiaye', 'Père', '456, Avenue Exemple, Dakar', '76 987 65 43']
];

// Ajouter les exemples
$row = 2;
foreach ($exampleData as $data) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Appliquer un style léger aux exemples
$styleExample = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'EEEEEE'],
    ],
    'font' => [
        'italic' => true,
        'color' => ['rgb' => '555555'],
    ],
];
$sheet->getStyle('A2:L3')->applyFromArray($styleExample);

// Ajouter un commentaire explicatif
$sheet->getComment('A1')->getText()->createTextRun('Champ obligatoire');
$sheet->getComment('F1')->getText()->createTextRun('Champ obligatoire - Utilisé pour identifier les doublons');
$sheet->getComment('G1')->getText()->createTextRun('Champ obligatoire');
$sheet->getComment('I1')->getText()->createTextRun('Champ obligatoire');
$sheet->getComment('L1')->getText()->createTextRun('Champ obligatoire');

// Ajuster la largeur des colonnes
foreach (range('A', 'L') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Créer le répertoire si nécessaire
$directory = 'assets/templates/';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

// Sauvegarder le fichier
$writer = new Xlsx($spreadsheet);
$writer->save($directory . 'modele_import_apprenants.xlsx');

echo "Modèle Excel créé avec succès dans $directory/modele_import_apprenants.xlsx";





 
// Voici les principales fonctions à modifier pour maintenir la compatibilité

/**
 * Récupère tous les apprenants à partir de la section utilisateurs
 */
function get_all_apprenants(): array {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    // Filtrer les utilisateurs ayant le profil "Apprenant" et possédant un matricule
    $apprenants = [];
    foreach ($data['utilisateurs'] as $utilisateur) {
        if (isset($utilisateur['profil']) && $utilisateur['profil'] === 'Apprenant' && isset($utilisateur['matricule'])) {
            $apprenants[] = $utilisateur;
        }
    }
    
    return $apprenants;
}

/**
 * Ajoute un nouvel apprenant à la section utilisateurs
 */
function ajouter_apprenant(array $apprenant_data): bool {
    global $model_tab;
    
    // Charger les données
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    // Trouver le dernier ID utilisateur
    $dernier_id = 0;
    foreach ($data['utilisateurs'] as $utilisateur) {
        if (isset($utilisateur['id']) && $utilisateur['id'] > $dernier_id) {
            $dernier_id = $utilisateur['id'];
        }
    }
    $nouvel_id = $dernier_id + 1;
    
    // Générer le matricule
    $matricule = 'APP-' . str_pad((string)$nouvel_id, 3, '0', STR_PAD_LEFT);
    
    // Générer ou utiliser le mot de passe fourni
    $mot_de_passe = $apprenant_data['password'] ?? generer_mot_de_passe();
    
    // Créer le nouvel apprenant avec le profil "Apprenant"
    $nouvel_apprenant = [
        'id' => $nouvel_id,
        'matricule' => $matricule,
        'prenom' => $apprenant_data['prenom'] ?? '',
        'nom' => $apprenant_data['nom'] ?? '',
        'date_naissance' => $apprenant_data['date_naissance'] ?? '',
        'lieu_naissance' => $apprenant_data['lieu_naissance'] ?? '',
        'adresse' => $apprenant_data['adresse'] ?? '',
        'email' => $apprenant_data['email'] ?? '',
        'login' => $apprenant_data['email'] ?? '', // Utiliser l'email comme login
        'telephone' => $apprenant_data['telephone'] ?? '',
        'referentiel' => $apprenant_data['referentiel'] ?? '',
        'photo' => $apprenant_data['photo'] ?? 'assets/images/default_avatar.jpg',
        'statut' => 'Actif',
        'tuteur_nom' => $apprenant_data['tuteur_nom'] ?? '',
        'lien_parente' => $apprenant_data['lien_parente'] ?? '',
        'tuteur_adresse' => $apprenant_data['tuteur_adresse'] ?? '',
        'tuteur_telephone' => $apprenant_data['tuteur_telephone'] ?? '',
        'password' => password_hash($mot_de_passe, PASSWORD_DEFAULT),
        'profil' => 'Apprenant' // Attribut important pour identifier le type d'utilisateur
    ];
    
    // Ajouter l'apprenant à la section utilisateurs
    $data['utilisateurs'][] = $nouvel_apprenant;
    
    // Sauvegarder les données
    $resultat = $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
    
    // Envoyer les identifiants si l'email est fourni
    if ($resultat && !empty($apprenant_data['email'])) {
        envoyer_identifiants_par_email($nouvel_apprenant, $mot_de_passe);
    }
    
    return $resultat;
}

function supprimer_apprenant(int $id): bool {
    global $model_tab;
    
    // Log le début de la fonction avec l'ID
    error_log("Début de la suppression de l'apprenant ID: " . $id);
    
    // Récupérer les données du JSON
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    // Débugger la structure des données
    error_log("Nombre d'utilisateurs dans data.json: " . count($data['utilisateurs']));
    
    $apprenant_trouve = false;
    $index_a_supprimer = -1;
    
    // Trouver l'apprenant à supprimer dans la section utilisateurs
    foreach ($data['utilisateurs'] as $index => $utilisateur) {
        if (isset($utilisateur['profil']) && $utilisateur['profil'] === 'Apprenant' && $utilisateur['id'] === $id) {
            $apprenant_trouve = true;
            $index_a_supprimer = $index;
            error_log("Apprenant trouvé à l'index: " . $index);
            break;
        }
    }
    
    if (!$apprenant_trouve) {
        error_log("Apprenant avec ID " . $id . " non trouvé dans la liste des utilisateurs");
        return false;
    }
    
    // Logique de suppression de la photo avec débogage étendu
    $photo_path = $data['utilisateurs'][$index_a_supprimer]['photo'] ?? '';
    error_log("Chemin de photo trouvé: " . $photo_path);
    
    if (!empty($photo_path) && $photo_path !== 'assets/images/default_avatar.jpg') {
        // Essayer plusieurs approches de chemin
        $absolute_photo_path = $_SERVER['DOCUMENT_ROOT'] . $photo_path;
        error_log("Chemin absolu construit: " . $absolute_photo_path);
        error_log("Le fichier existe? " . (file_exists($absolute_photo_path) ? "Oui" : "Non"));
        
        // Essayer aussi le chemin direct
        error_log("Le fichier existe (chemin direct)? " . (file_exists($photo_path) ? "Oui" : "Non"));
        
        // Essayer de supprimer avec le chemin absolu
        if (file_exists($absolute_photo_path)) {
            $unlink_result = unlink($absolute_photo_path);
            error_log("Résultat de unlink (absolu): " . ($unlink_result ? "Succès" : "Échec"));
        } 
        // Essayer de supprimer avec le chemin direct comme fallback
        else if (file_exists($photo_path)) {
            $unlink_result = unlink($photo_path);
            error_log("Résultat de unlink (direct): " . ($unlink_result ? "Succès" : "Échec"));
        }
    }
    
    // Supprimer l'apprenant du tableau des utilisateurs
    error_log("Suppression de l'apprenant de l'array à l'index " . $index_a_supprimer);
    array_splice($data['utilisateurs'], $index_a_supprimer, 1);
    
    // Sauvegarder les données mises à jour dans le JSON
    $result = $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, CheminPage::DATA_JSON->value);
    error_log("Résultat de l'écriture JSON: " . ($result ? "Succès" : "Échec"));
    
    return $result;
}
/**
 * Vérifie si un email existe déjà parmi les utilisateurs
 */
function email_existe_deja(string $email): bool {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    foreach ($data['utilisateurs'] as $utilisateur) {
        if (strtolower($utilisateur['email'] ?? '') === strtolower($email)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Récupère un apprenant par son ID depuis la section utilisateurs
 */
function get_apprenant_by_id($id) {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    
    foreach ($data['utilisateurs'] as $utilisateur) {
        if (isset($utilisateur['profil']) && $utilisateur['profil'] === 'Apprenant' && $utilisateur['id'] == $id) {
            return $utilisateur;
        }
    }
    
    return null;
}