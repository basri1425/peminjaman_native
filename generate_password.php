<?php

echo "<h3>Password Hash</h3>";

echo "admin123 <br>";
echo password_hash("admin123", PASSWORD_DEFAULT);

echo "<hr>";

echo "petugas123 <br>";
echo password_hash("petugas123", PASSWORD_DEFAULT);

echo "<hr>";

echo "peminjam123 <br>";
echo password_hash("peminjam123", PASSWORD_DEFAULT);