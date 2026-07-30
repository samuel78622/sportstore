<!-- PUBLIC FOOTER -->
<footer class="footer-sportstore">
    <div class="container py-5">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="footer-title">SPORTSTORE</h5>
                <p class="footer-text">Tu tienda de ropa deportiva de confianza. Encuentra todo lo que necesitas para entrenar sin límites.</p>
            </div>

            <!-- Links Section -->
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="footer-title">Enlaces Rápidos</h5>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="carrito.php">Carrito</a></li>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="cliente/perfil.php">Mi Perfil</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Contact Section -->
            <div class="col-md-4">
                <h5 class="footer-title">Contacto</h5>
                <div class="footer-contact">
                    <p><i class="fas fa-phone"></i> +57 (1) 2345-6789</p>
                    <p><i class="fas fa-envelope"></i> <a href="mailto:info@sportstore.com">info@sportstore.com</a></p>
                    <p><i class="fas fa-map-marker-alt"></i> Bogotá, Colombia</p>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Copyright -->
        <div class="text-center">
            <p class="footer-copyright">&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
        </div>
    </div>
</footer>
