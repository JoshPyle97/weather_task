<?php
//db connection
    $servername = "localhost";
    $username = "root";
    $password = "mysql";
    $dbname = "weather";

    $conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

//function initLoad()
//{
//    $ip_url = file_get_contents("https://api.ipify.org?format=json");
//    $ip_address = json_decode($ip_url, true);
//    $ip_address['ip'] = $_GET['ip_address'];
//}

//api for ip location
    if($_GET['ip_address']) {
        $location_url = file_get_contents("https://geo.ipify.org/api/v2/country,city?apiKey=at_vSCeGTxYqkvDXPM7fy2689z9HVVLA&ipAddress=" . $_GET['ip_address']);
        $location = json_decode($location_url, true);
        $lat = $location['location']['lat'];
        $lng = $location['location']['lng'];
        $ip = $location['ip'];
//formatting data
        $country = $location['location']['country'];
        $region = $location['location']['region'];
        $city = $location['location']['city'];

        $location_string = "Weather for " . $city . ", " . $region . ", " . $country;

        $weather = "";
        $error = "";

        //api for weather
        $urlContents = file_get_contents("http://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lng&appid=4b6cbadba309b7554491c5dc66401886");

        $weatherArray = json_decode($urlContents, true);
        $date = date("Y-m-d H:i:s");
        $guest_data = serialize(['ip' => $ip, 'datetime' => $date]);

        $sql = "INSERT INTO weather_data (id, guest_data, weather_data)
        VALUES(0, '$guest_data', '$urlContents')";

        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }

//Weather data returns in 3 hour periods, so app just uses the time for midday as the day
        if ($weatherArray['cod'] == 200) {
            $i = 0;
            $d = 0;
            foreach ($weatherArray['list'] as $weather) {

                $noon = $weatherArray['list'][$i]['dt'];

                $noon_check = date("H:i:s", $noon);

                if ($noon_check == "12:00:00") {

                    $weather = 1;

                    $weather_icon[$d] = $weatherArray['list'][$i]['weather'][0]['icon'];
                    $weather_icon_url[$d] = "<img src='http://openweathermap.org/img/wn/$weather_icon[$d]@2x.png'";
                    $weather_main[$d] = $weatherArray['list'][$i]['weather'][0]['main'];
                    $weather_temp[$d] = $weatherArray['list'][$i]['main']['temp'];
                    $day[$d] = date("l", $noon);

                    $d++;
                }
                $i++;
            }


        } else {
            $error = "Data not available";
        }
    }



?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">

    <title>Weather App</title>
</head>
<body>
<div class="container">

    <h1>Weather</h1>



    <form>
        <fieldset class="form-group">
            <label for="city">IP Address</label>
            <input type="text" class="form-control" name="ip_address" id="city" placeholder="IP Address" value = "<?php echo $_GET['ip_address']; ?>">
        </fieldset>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <div id="location">
        <h1><? if ($weather) {
                echo $location_string;
            }?></h1>
    </div>
    <div id="weather">
        <table style="width:100%">
            <tr>
                <th><? echo $day[0]; ?></th>
                <th><? echo $day[1]; ?></th>
                <th><? echo $day[2]; ?></th>
                <th><? echo $day[3]; ?></th>
                <th><? echo $day[4]; ?></th>
            </tr>
            <tr>
                <td><? echo $weather_icon_url[0]; ?></td>
                <td><? echo $weather_icon_url[1]; ?></td>
                <td><? echo $weather_icon_url[2]; ?></td>
                <td><? echo $weather_icon_url[3]; ?></td>
                <td><? echo $weather_icon_url[4]; ?></td>
            </tr>
            <tr>
                <td><? echo $weather_temp[0]; ?></td>
                <td><? echo $weather_temp[1]; ?></td>
                <td><? echo $weather_temp[2]; ?></td>
                <td><? echo $weather_temp[3]; ?></td>
                <td><? echo $weather_temp[4]; ?></td>
            </tr>
            <tr>
                <td><? echo $weather_main[0]; ?></td>
                <td><? echo $weather_main[1]; ?></td>
                <td><? echo $weather_main[2]; ?></td>
                <td><? echo $weather_main[3]; ?></td>
                <td><? echo $weather_main[4]; ?></td>
            </tr>
        </table>

    </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script>
</body>

</html>