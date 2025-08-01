# Customer Bulk Upload Feature

## Overview
The bulk upload feature allows you to import multiple customers at once using a CSV file. This feature is accessible from the Customers index page via the "Bulk Upload" button.

## Features
- **CSV Upload**: Upload customer data in CSV format
- **Sample Download**: Download a sample CSV file with the correct format
- **Cash Collateral**: Option to apply cash collateral to all uploaded customers
- **Validation**: Comprehensive validation of CSV data
- **Error Handling**: Detailed error reporting for failed rows
- **Progress Feedback**: Visual feedback during upload process

## How to Use

### 1. Access Bulk Upload
- Navigate to Customers → Click "Bulk Upload" button

### 2. Download Sample CSV
- Click "Download Sample CSV" to get the template
- The sample file contains:
  - Header row with column names
  - Two example customer records
  - Required and optional field examples

### 3. Prepare Your CSV File
The CSV file must contain the following columns:

**Required Columns:**
- `name` - Customer's full name
- `phone1` - Primary phone number
- `dob` - Date of birth (YYYY-MM-DD format)
- `sex` - Gender (M or F)

**Optional Columns:**
- `phone2` - Secondary phone number
- `work` - Occupation
- `workaddress` - Work address
- `idtype` - ID type (e.g., National ID, License)
- `idnumber` - ID number
- `relation` - Relationship (e.g., Spouse, Parent)
- `description` - Customer description
- `region_id` - Region ID (can be added later)
- `district_id` - District ID (can be added later)

### 4. Upload Process
1. Select your prepared CSV file
2. Choose cash collateral options (optional):
   - Check "Apply Cash Collateral to All Customers"
   - Select collateral type if checked
3. Click "Upload Customers"
4. Wait for processing (button will show "Uploading...")
5. Review results

### 5. Results
- **Success**: Redirected to customers list with success message
- **Errors**: Stay on upload page with detailed error messages
- **Partial Success**: Warning message with list of failed rows

## CSV Format Example
```csv
name,phone1,phone2,dob,sex,work,workaddress,idtype,idnumber,relation,description
John Doe,0712345678,0755123456,1990-01-15,M,Teacher,ABC School,National ID,123456789,Spouse,Sample customer
Jane Smith,0723456789,,1985-05-20,F,Nurse,City Hospital,License,987654321,Parent,Another sample
```

## Validation Rules
- File size: Maximum 5MB
- File type: CSV only
- Required fields: name, phone1, dob, sex
- Sex values: M or F only
- Date format: YYYY-MM-DD
- Region and District can be added later through customer edit

## Error Handling
The system provides detailed error messages for:
- Missing required columns
- Invalid data formats
- Missing required fields
- Database constraint violations

## Security Features
- File type validation
- File size limits
- SQL injection prevention
- Transaction rollback on errors
- User authentication required

## Notes
- All customers get default password: 12345
- Customer numbers are auto-generated
- Branch and company are set from logged-in user
- Registrar is set to current user
- Registration date is set to current date
- Cash collateral amount is set to 0 initially
- Region and District can be updated later through customer edit form 