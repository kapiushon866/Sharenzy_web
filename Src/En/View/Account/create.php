<?php
session_start();

$host = "sql5.freesqldatabase.com"; 
$user = "sql5767007";           
$pass = "jLRCnZ3l23";            
$dbname = "sql5767007";            

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


function generateAccountToken($length = 344) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $token = '';

    for ($i = 0; $i < $length - 2; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $token . '==';
}

function getHwid() {
    $cpuId = trim(shell_exec('wmic cpu get ProcessorId'));
    $diskSerial = trim(shell_exec('wmic diskdrive get serialnumber'));
    $hwid = md5($cpuId . $diskSerial);
    
    return $hwid;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? null;
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $birthday = isset($_POST['birthday']) ? date('Y-m-d', strtotime($_POST['birthday'])) : null;
    $gender = $_POST['gender'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirm_password = $_POST['confirm-password'] ?? null;
    $country = $_POST['country'] ?? null;

    $errors = [];

    if ($phone) {
        $phone = preg_replace('/\D/', '', $phone); 
        if (strlen($phone) == 10) {
            $phone = '('.substr($phone, 0, 3).')-'.substr($phone, 3, 3).'-'.substr($phone, 6);
        } else {
            $errors[] = "Invalid phone number.";
        }
    } else {
        $errors[] = "Phone number is required.";
    }

    if (!$first_name || !$last_name) $errors[] = "First name and last name are required!";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match!";

    if (!empty($errors)) {
        echo implode("<br>", $errors);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    do {
        $account_token = generateAccountToken();
        $uuid = uniqid('', true);

        $stmt = $conn->prepare("SELECT id FROM users WHERE account_token = ? OR uuid = ?");
        $stmt->bind_param("ss", $account_token, $uuid);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0); 

    $stmt->close();

    $stmt = $conn->prepare("SELECT username, email, phone_number FROM users WHERE username = ? OR email = ? OR phone_number = ?");
    $stmt->bind_param("sss", $username, $email, $phone);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($existing_username, $existing_email, $existing_phone);

    while ($stmt->fetch()) {
        if ($existing_username === $username) $errors[] = "Username already exists.";
        if ($existing_email === $email) $errors[] = "Email already exists.";
        if ($existing_phone === $phone) $errors[] = "Phone number already exists.";
    }
    $stmt->close();

    if (!empty($errors)) {
        echo implode("<br>", $errors);
        $conn->close();
        exit;
    }

    $created_at = date('Y-m-d h:i:s A');
    $updated_at = date('Y-m-d h:i:s A');

    // Get IPv4
    $registered_ipv4 = $_SERVER['REMOTE_ADDR'];
    if ($registered_ipv4 == '::1' || $registered_ipv4 == '127.0.0.1') {
        $registered_ipv4 = '127.0.0.1';  // Default local IP
    }

    // Get IPv6 (try to fetch the correct client IPv6)
    $registered_ipv6 = $_SERVER['REMOTE_ADDR'];  

    // Get HWID (Placeholder function)
    $registered_hwid = getHwid();

    $stmt = $conn->prepare("INSERT INTO users (created_at, updated_at, account_token, uuid, username, first_name, last_name, email, phone_number, birthday, gender, password, country, registered_ipv4, registered_ipv6, registered_hwid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssss", $created_at, $updated_at, $account_token, $uuid, $username, $first_name, $last_name, $email, $phone, $birthday, $gender, $hashed_password, $country, $registered_ipv4, $registered_ipv6, $registered_hwid);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharenzy</title>
    <link rel="stylesheet" href="../../../Engine/Ui/Main/main.css">
    <link rel="stylesheet" href="../../../Engine/Ui/Account/create.css"> 
    <script src="../../../Engine/Scripts/main_sidebar.js" defer></script>
    <script src="../../../Engine/Scripts/phone_format.js" defer></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Create Account</h1>
        </header>

        <nav class="main-nav-sidebar" id="sidebar">
            <div class="menu-icon" id="menu-icon">
                <img src="../../../Engine/Images/Svg/Menu.svg" alt="Menu Icon" width="24" height="24">
            </div>
        
            <a href="View/Account/login.php" class="account-icon">
                <img src="../../../Engine/Images/Svg/Account.svg" alt="Account Icon" width="40" height="40">
            </a>
        
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="#profile">Profile</a></li>
                <li><a href="#messages">Messages</a></li>
                <li><a href="#explore">Explore</a></li>
                <li><a href="#settings">Settings</a></li>
                <li><a href="View/Terms/terms_of_service.html">Terms Of Service</a></li>
            </ul>
        </nav>
        
        <div class="create-card">
            <form class="create-form" method="POST" action="create.php">
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="input-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first_name" required>
                </div>

                <div class="input-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last_name" required>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="input-group">
    <label for="phone">Phone Number</label>
    <input type="tel" id="phone" name="phone" maxlength="12" placeholder="555 555 5555" required>
    <span id="error-message" style="color: red; display: none;">Please enter the phone number in the format: 555 555 5555.</span>
</div>



                <div class="input-group">
                    <label for="birthday">Birthday</label>
                    <input type="date" id="birthday" name="birthday" required>
                </div>

                <div class="input-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>

                <div class="input-group">
                <label for="country">Country</label>
    <select id="country" name="country" required>
        <option value="Afghanistan">Afghanistan</option>
        <option value="Albania">Albania</option>
        <option value="Algeria">Algeria</option>
        <option value="Andorra">Andorra</option>
        <option value="Angola">Angola</option>
        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
        <option value="Argentina">Argentina</option>
        <option value="Armenia">Armenia</option>
        <option value="Australia">Australia</option>
        <option value="Austria">Austria</option>
        <option value="Azerbaijan">Azerbaijan</option>
        <option value="Bahamas">Bahamas</option>
        <option value="Bahrain">Bahrain</option>
        <option value="Bangladesh">Bangladesh</option>
        <option value="Barbados">Barbados</option>
        <option value="Belarus">Belarus</option>
        <option value="Belgium">Belgium</option>
        <option value="Belize">Belize</option>
        <option value="Benin">Benin</option>
        <option value="Bhutan">Bhutan</option>
        <option value="Bolivia">Bolivia</option>
        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
        <option value="Botswana">Botswana</option>
        <option value="Brazil">Brazil</option>
        <option value="Brunei">Brunei</option>
        <option value="Bulgaria">Bulgaria</option>
        <option value="Burkina Faso">Burkina Faso</option>
        <option value="Burundi">Burundi</option>
        <option value="Cabo Verde">Cabo Verde</option>
        <option value="Cambodia">Cambodia</option>
        <option value="Cameroon">Cameroon</option>
        <option value="Canada">Canada</option>
        <option value="Central African Republic">Central African Republic</option>
        <option value="Chad">Chad</option>
        <option value="Chile">Chile</option>
        <option value="China">China</option>
        <option value="Colombia">Colombia</option>
        <option value="Comoros">Comoros</option>
        <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
        <option value="Costa Rica">Costa Rica</option>
        <option value="Croatia">Croatia</option>
        <option value="Cuba">Cuba</option>
        <option value="Cyprus">Cyprus</option>
        <option value="Czech Republic (Czechia)">Czech Republic (Czechia)</option>
        <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option>
        <option value="Denmark">Denmark</option>
        <option value="Djibouti">Djibouti</option>
        <option value="Dominica">Dominica</option>
        <option value="Dominican Republic">Dominican Republic</option>
        <option value="Ecuador">Ecuador</option>
        <option value="Egypt">Egypt</option>
        <option value="El Salvador">El Salvador</option>
        <option value="Equatorial Guinea">Equatorial Guinea</option>
        <option value="Eritrea">Eritrea</option>
        <option value="Estonia">Estonia</option>
        <option value="Eswatini (fmr. &quot;Swaziland&quot;)">Eswatini (fmr. &quot;Swaziland&quot;)</option>
        <option value="Ethiopia">Ethiopia</option>
        <option value="Fiji">Fiji</option>
        <option value="Finland">Finland</option>
        <option value="France">France</option>
        <option value="Gabon">Gabon</option>
        <option value="Gambia">Gambia</option>
        <option value="Georgia">Georgia</option>
        <option value="Germany">Germany</option>
        <option value="Ghana">Ghana</option>
        <option value="Greece">Greece</option>
        <option value="Grenada">Grenada</option>
        <option value="Guatemala">Guatemala</option>
        <option value="Guinea">Guinea</option>
        <option value="Guinea-Bissau">Guinea-Bissau</option>
        <option value="Guyana">Guyana</option>
        <option value="Haiti">Haiti</option>
        <option value="Honduras">Honduras</option>
        <option value="Hungary">Hungary</option>
        <option value="Iceland">Iceland</option>
        <option value="India">India</option>
        <option value="Indonesia">Indonesia</option>
        <option value="Iran">Iran</option>
        <option value="Iraq">Iraq</option>
        <option value="Ireland">Ireland</option>
        <option value="Israel">Israel</option>
        <option value="Italy">Italy</option>
        <option value="Jamaica">Jamaica</option>
        <option value="Japan">Japan</option>
        <option value="Jordan">Jordan</option>
        <option value="Kazakhstan">Kazakhstan</option>
        <option value="Kenya">Kenya</option>
        <option value="Kiribati">Kiribati</option>
        <option value="Korea, North">Korea, North</option>
        <option value="Korea, South">Korea, South</option>
        <option value="Kuwait">Kuwait</option>
        <option value="Kyrgyzstan">Kyrgyzstan</option>
        <option value="Laos">Laos</option>
        <option value="Latvia">Latvia</option>
        <option value="Lebanon">Lebanon</option>
        <option value="Lesotho">Lesotho</option>
        <option value="Liberia">Liberia</option>
        <option value="Libya">Libya</option>
        <option value="Liechtenstein">Liechtenstein</option>
        <option value="Lithuania">Lithuania</option>
        <option value="Luxembourg">Luxembourg</option>
        <option value="Madagascar">Madagascar</option>
        <option value="Malawi">Malawi</option>
        <option value="Malaysia">Malaysia</option>
        <option value="Maldives">Maldives</option>
        <option value="Mali">Mali</option>
        <option value="Malta">Malta</option>
        <option value="Marshall Islands">Marshall Islands</option>
        <option value="Mauritania">Mauritania</option>
        <option value="Mauritius">Mauritius</option>
        <option value="Mexico">Mexico</option>
        <option value="Micronesia">Micronesia</option>
        <option value="Moldova">Moldova</option>
        <option value="Monaco">Monaco</option>
        <option value="Mongolia">Mongolia</option>
        <option value="Montenegro">Montenegro</option>
        <option value="Morocco">Morocco</option>
        <option value="Mozambique">Mozambique</option>
        <option value="Myanmar (formerly Burma)">Myanmar (formerly Burma)</option>
        <option value="Namibia">Namibia</option>
        <option value="Nauru">Nauru</option>
        <option value="Nepal">Nepal</option>
        <option value="Netherlands">Netherlands</option>
        <option value="New Zealand">New Zealand</option>
        <option value="Nicaragua">Nicaragua</option>
        <option value="Niger">Niger</option>
        <option value="Nigeria">Nigeria</option>
        <option value="North Macedonia">North Macedonia</option>
        <option value="Norway">Norway</option>
        <option value="Oman">Oman</option>
        <option value="Pakistan">Pakistan</option>
        <option value="Palau">Palau</option>
        <option value="Panama">Panama</option>
        <option value="Papua New Guinea">Papua New Guinea</option>
        <option value="Paraguay">Paraguay</option>
        <option value="Peru">Peru</option>
        <option value="Philippines">Philippines</option>
        <option value="Poland">Poland</option>
        <option value="Portugal">Portugal</option>
        <option value="Qatar">Qatar</option>
        <option value="Romania">Romania</option>
        <option value="Russia">Russia</option>
        <option value="Rwanda">Rwanda</option>
        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
        <option value="Saint Lucia">Saint Lucia</option>
        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
        <option value="Samoa">Samoa</option>
        <option value="San Marino">San Marino</option>
        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
        <option value="Saudi Arabia">Saudi Arabia</option>
        <option value="Senegal">Senegal</option>
        <option value="Serbia">Serbia</option>
        <option value="Seychelles">Seychelles</option>
        <option value="Sierra Leone">Sierra Leone</option>
        <option value="Singapore">Singapore</option>
        <option value="Slovakia">Slovakia</option>
        <option value="Slovenia">Slovenia</option>
        <option value="Solomon Islands">Solomon Islands</option>
        <option value="Somalia">Somalia</option>
        <option value="South Africa">South Africa</option>
        <option value="South Sudan">South Sudan</option>
        <option value="Spain">Spain</option>
        <option value="Sri Lanka">Sri Lanka</option>
        <option value="Sudan">Sudan</option>
        <option value="Suriname">Suriname</option>
        <option value="Sweden">Sweden</option>
        <option value="Switzerland">Switzerland</option>
        <option value="Syria">Syria</option>
        <option value="Taiwan">Taiwan</option>
        <option value="Tajikistan">Tajikistan</option>
        <option value="Tanzania">Tanzania</option>
        <option value="Thailand">Thailand</option>
        <option value="Timor-Leste">Timor-Leste</option>
        <option value="Togo">Togo</option>
        <option value="Tonga">Tonga</option>
        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
        <option value="Tunisia">Tunisia</option>
        <option value="Turkey">Turkey</option>
        <option value="Turkmenistan">Turkmenistan</option>
        <option value="Tuvalu">Tuvalu</option>
        <option value="Uganda">Uganda</option>
        <option value="Ukraine">Ukraine</option>
        <option value="United Arab Emirates">United Arab Emirates</option>
        <option value="United Kingdom">United Kingdom</option>
        <option value="United States of America">United States of America</option>
        <option value="Uruguay">Uruguay</option>
        <option value="Uzbekistan">Uzbekistan</option>
        <option value="Vanuatu">Vanuatu</option>
        <option value="Vatican City">Vatican City</option>
        <option value="Venezuela">Venezuela</option>
        <option value="Vietnam">Vietnam</option>
        <option value="Yemen">Yemen</option>
        <option value="Zambia">Zambia</option>
        <option value="Zimbabwe">Zimbabwe</option>
                    </select>
                </div>

                <div class="input-group">
                <button type="submit" class="create-btn">Create Account</button>
                </div>
            </form>
            <div class="create-account">
                <p>Already Have An Account? <a href="login.php" class="link-btn">Log In Here</a></p>
            </div>      
        </div>
    </div>

    <footer>
            <p>© 2025 Sharenzy. All Rights Reserved.</p>
        </footer>
</body>
</html>
