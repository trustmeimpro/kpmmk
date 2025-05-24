<?php
// Function to get an appropriate image URL based on item name
function getItemImageUrl($itemName) {
    // Base path for the assets folder
    $basePath = '/Tugas KP/assets/images/';
    
    // Map of keywords to image filenames in the assets folder
    $imageMap = [
        'labu erlenmeyer' => 'erlenmeyer.jpg',
        'gelas kimia' => 'gelas_kimia.jpg',
        'plate tetes' => 'plate_tetes.jpg',
        'gelas ukur' => 'gelas_ukur.jpg',
        'tabung reaksi' => 'tabung_reaksi.jpg',
        'rak tabung' => 'rak_tabung.jpg',
        'penjepit' => 'penjepit.jpg',
        'pipet' => 'pipet.jpg',
        'tabung spiritus' => 'tabung_spiritus.jpg',
        'corong' => 'corong.jpg',
        'cawan petri' => 'cawan_petri.jpg',
        'kaki tiga' => 'kaki_tiga.jpg'
    ];
    
    // Default image if no match is found
    $defaultImage = $basePath . 'default_lab_item.jpg';
    
    // Convert item name to lowercase for case-insensitive matching
    $itemNameLower = strtolower($itemName);
    
    // Check for matches in the image map
    foreach ($imageMap as $keyword => $imageFile) {
        if (strpos($itemNameLower, $keyword) !== false) {
            // Check if the image file exists
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $basePath . $imageFile;
            if (file_exists($imagePath)) {
                return $basePath . $imageFile;
            } else {
                // If the specific image doesn't exist, use the default
                return $defaultImage;
            }
        }
    }
    
    // Return default image if no match is found
    return $defaultImage;
}
?>