# Macrame CLI

## CRUDs
To create the required files for a new CRUD run the following commands in both of the Macrame project folders:
```sh
macli make:crud fruit 
```

### Laravel Files
Within the **Laravel Admin** project folder this will create the files:

`/admin`
  - `admin/Http/Controllers/FruitController.php`
  - `admin/Http/Indexes/FruitIndex.php`
  - `admin/Http/Resources/FruitResource.php`

If these files don't already exist it will also try to create app files:

`/app`
  - `app/Http/Controller/FruiController.php`
  - `app/Http/Resources/FruitResource.php`
  - `app/Models/Fruit.php`

`/database/migrations`
  - `...fruits_table.php`

Leaving only the view part to be created by yourself. 

### Vue Files
Within the **Admin Vue** project folder the command will generate:

- `src/Pages/fruit/Index.vue`
- `src/Pages/fruit/Show.vue`
- `src/Pages/fruit/routes.ts `
- `src/Pages/fruit/components/AddFruitModal.vue `
- `src/entities/fruit/api.ts`
- `src/entities/fruit/crud.form.ts`
- `src/entities/fruit/crud.index.ts`
- `src/entities/fruit/types.ts`

It will also edit `src/entities/index.ts` to register the entity files and `src/plugins/router.ts` to register the required routes.

