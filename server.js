const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json()); // Para poder recibir JSON en las peticiones

// Configuración de la conexión a la base de datos
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root', // Usuario por defecto en XAMPP
  password: '', // Contraseña por defecto en XAMPP
  database: 'cinelumiere_db'
});

db.connect(err => {
  if (err) {
    console.error('Error conectando a la base de datos:', err);
    return;
  }
  console.log('Conectado a la base de datos MySQL.');
});

// --- RUTAS DE LA API ---

// Obtener todas las películas
app.get('/api/peliculas', (req, res) => {
  const sql = 'SELECT * FROM peliculas';
  db.query(sql, (err, results) => {
    if (err) {
      res.status(500).send(err);
    } else {
      res.json(results);
    }
  });
});

app.get('/api/dulceria', (req, res) => {
  const sql = 'SELECT * FROM dulceria'; // Usamos la tabla que creamos
  db.query(sql, (err, results) => {
    if (err) {
      res.status(500).send(err);
    } else {
      res.json(results);
    }
  });
});

// Registrar un nuevo usuario
app.post('/api/register', (req, res) => {
    const { email, password } = req.body;
    // En una aplicación real, DEBES encriptar la contraseña.
    // const hashedPassword = bcrypt.hashSync(password, 8);
    const sql = 'INSERT INTO usuarios (email, password) VALUES (?, ?)';
    db.query(sql, [email, password], (err, result) => {
        if (err) {
            // Manejar error, por ejemplo, si el email ya existe
            res.status(500).json({ message: 'Error al registrar el usuario', error: err });
        } else {
            res.status(201).json({ message: 'Usuario registrado con éxito', userId: result.insertId });
        }
    });
});

// Validar inicio de sesión (login)
app.post('/api/login', (req, res) => {
    const { email, password } = req.body;
    const sql = 'SELECT * FROM usuarios WHERE email = ?';
    db.query(sql, [email], (err, results) => {
        if (err || results.length === 0) {
            return res.status(401).json({ message: 'Credenciales inválidas' });
        }
        
        const user = results[0];
        // En una app real, compara la contraseña encriptada
        // const passwordIsValid = bcrypt.compareSync(password, user.password);
        if (password !== user.password) {
            return res.status(401).json({ message: 'Credenciales inválidas' });
        }

        res.json({ message: 'Inicio de sesión exitoso', userId: user.id });
    });
});


// La ruta para guardar compras que ya teníamos
app.post('/api/compras', (req, res) => {
    // La expandiremos para recibir más detalles
    const { pelicula_id, usuario_id, asientos, total } = req.body;
    const sql = 'INSERT INTO compras (pelicula_id, usuario_id, asientos, total_pagado) VALUES (?, ?, ?, ?)';
    // Necesitarás añadir las columnas 'asientos' y 'total_pagado' a tu tabla 'compras'
    db.query(sql, [pelicula_id, usuario_id, asientos, total], (err, result) => {
        if (err) {
            res.status(500).send(err);
        } else {
            res.status(201).json({ message: 'Compra confirmada', compraId: result.insertId });
    }
  });
});

const PORT = 5000;
app.listen(PORT, () => {
  console.log(`Servidor corriendo en el puerto ${PORT}`);
});