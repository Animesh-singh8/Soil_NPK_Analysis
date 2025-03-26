## **Steps to Run the Soil NPK Analysis Project Locally**  

### **Step 1: Install XAMPP**  
- Download and install **[XAMPP](https://www.apachefriends.org/download.html)**.  
- Open **XAMPP Control Panel** and **start Apache and MySQL**.  

### **Step 2: Download the Project Files**  
- Clone the repository using Git:  
  ```sh
  git clone https://github.com/Animesh-singh8/Soil_NPK_Analysis.git
  ```  
  OR  
- Download the project as a ZIP file from GitHub and extract it into:  
  ```
  C:/xampp/htdocs/Soil_NPK_Analysis
  ```  

 **Step 3: Set Up the Database**  
1. Open your browser and go to **phpMyAdmin**:  
   ```
   http://localhost/phpmyadmin/
   ```  
2. Click on **Databases** → Create a new database named:  
   ```
   soil_npk
   ```  
3. Click on the **Import** tab → Select `database/soil_npk.sql` file → Click **Go**.  

Step 4: Configure Database Connection
1. Open the project folder `Soil_NPK_Analysis/includes/db_connect.php`.  
2. Ensure these credentials match your local XAMPP setup:  
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "soil_npk";
   ```  

 **Step 5: Run the Project**  
- Open a browser and go to:  
  ```
  http://localhost/Soil_NPK_Analysis/
  ```  
- Start using the **Soil NPK Analysis System**.  
