--------------------------------------------------------------------------------
                        Drupal 8 RdfUI module
--------------------------------------------------------------------------------

CONTENTS
=====================

 * Introduction
 * Installation
 * Usage
 * Author


INTRODUCTION
============
Drupal 8 RdfUI module provides User Interfaces for site builders to integrate
schema.org seamlessly during the site building process.

INSTALLATION
============
The module only depends on modules in Drupal 8 core.

To install rdfui Module:
  * Place this module directory in your modules folder (this will usually be
    "modules/").
  * Enable the module within your Drupal site at Administer -> Extend (admin/modules)



USAGE
=====
Content types could be mapped at the point of creation (admin/structure/types/add)
or later (by using the edit form for content type) by specifying the type to be
mapped under the "Schema.org Mappings" menu link. The combobox provided for the
selection lists all the types from Schema.org and supports autocompletion.

Fields can be mapped with properties using the user interface
(admin/structure/types/manage/{node_type}/fields/rdf). To navigate to this form
you should select 'Manage Fields' operation for the Content type you desire and
then the secondary tab 'RDF Mappings'. Schema.org properties are listed under
RDF-predicate which is mapped to fields listed.

If the chosen Content type is not mapped to a Schema.org type, all the properties
are listed as options for the fields of that Content Type.

For a mapped Content Type, only the properties of the chosen Schema.org type will
be available as options.



