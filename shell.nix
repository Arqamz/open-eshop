{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  packages = [
    pkgs.php84
    pkgs.php84Packages.composer
  ];

  shellHook = ''
    echo "Php version $(php --version)"
    echo "Composer version $(composer --version)"
    echo "Environment ready. Use 'php artisan serve' to start the server."
  '';
}
