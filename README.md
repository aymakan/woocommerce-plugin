# Wordpress plugin for Aymakan Integration
This plugin enables WordPress WooCommerce stores to perform the following.

- Create AWB in Aymakan
- Add a shipment to WooCommerce Order with tracking number and AWB download link

## Installation
Following are the instruction to install this plugin.

- Download this repository. 
- Upload the Aymakana plugin zip file through WordPress admin.

![Configuration](screenshots/screenshot_1.png?raw=true "Configuration")

## Configuring Plugin
After installation, go to WooCommerce --> Settings --> Shipping --> Aymakan Shipping. Enable shipping method and fill all the information.

![Configuration](screenshots/screenshot_2.png?raw=true "Configuration")

There are some key configurations to note down.

- `Test Mode`: If you are testing the plugin `Checked` the box. Once the integration is tested, and ready to move to production
disable `Test Mode`, by unchecking.
- `API Key`:  API Key is used for authenticating with Aymakan Api. The API key can be found in your Aymakan account.
Login to your account and go to `Integrations`. Copy the Api Key and paste in the API Key field in Aymakan plugin configuration.
- `Collection Related Data`: As can be seen in above screenshot, there are several config fields which are Collection related. 
These fields are related to your address (From where Aymakan drivers will be picking up shipments). Enter your contact information here
or enter your Warehouse address and contact information in all those fields accordingly.

## Usage
Once the plugin is configured properly, its time to see it in action. 

- Go to orders and open an order which is pending
or which can be shipped. You will be able to see `Create Aymakan Shipping` button under the shipping section. Check below screenshot.

![Create Aymakan Shipping Button](screenshots/screenshot_3.png?raw=true "Create Aymakan Shipping Button")

- By clicking on this button, the following form will display in popup.

![Create Aymakan Shipping Form](screenshots/screenshot_4.png?raw=true "Create Shipping Form")

- Most of the form will be already filled up for you. You will need to select a `Delivery City`. Aymakan 
only support a list of cities with proper namings. So select the desired city.
- If order is COD, then select `Yes` in `Is COD?` field.
- if order is COD, the `COD Amount` field will already have the order total. Confirm if it is correct.
- Items field should have the total number of items (products) in this shipment.
- Pieces field should have number of pieces this shipment will have. For example, for a large shipment, 
there will be several items not fitting in a single carton, so they will be packed in multiple cartons. This field
should have the number of cartons.
- Click on `Create Shipping` button at bottom right to create a shipping in Aymakan. 
- Once the shipment is created, you will have a success message and an order note will appear on the right side of order page with View PDF (AWB) link.
