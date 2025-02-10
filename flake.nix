{
  description = "Dev shell for Php Laravel";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs/nixos-unstable";
  };

  outputs = { self, nixpkgs, ...}: let
    pkgs = nixpkgs.legacyPackages."x86_64-linux";
  in {
    devShells.x86_64-linux.default = pkgs.mkShell {
      packages = [
        pkgs.php84
        pkgs.php84Packages.composer
      ];

      shellHook = ''
          echo "Php version $(php --version)"
          echo "Composer version $(composer --version)"
          echo "Environment ready. Use 'php artisan serve' to start the server."
      '';
    };
  };
}