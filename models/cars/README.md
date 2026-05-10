# 3D models for cars

Use one of two approaches:

1. Default naming (no extra config)
- Put files as:
  - `models/cars/car-1.glb`
  - `models/cars/car-2.glb`
  - ...
  - `models/cars/car-20.glb`

2. Custom mapping via `cars_3d_models.php`
- Add entry:
  - `1 => 'https://your-cdn.com/models/toyota-camry.glb'`
  - or `1 => 'models/cars/custom-toyota.glb'`

Supported format in viewer: `.glb` (recommended).
