export const formTableColumns = [
  {
    title: 'Sub Category',
    key: 'subCategory',
    dataIndex: 'subCategory',
    className: 'column-sub-category',
    width: 280,
  },
  {
    title: 'Performance Indicator',
    key: 'name',
    dataIndex: 'name',
    className: 'column-pi-name',
    width: 450,
  },
  {
    title: 'is Header PI?',
    key: 'isHeader',
    dataIndex: 'isHeader',
    className: 'column-is-header',
    width: 100,
  },
  {
    title: 'Success Indicators',
    className: 'column-success-indicator',
    width: 400,
    children: [
      {
        title: 'Target (per Fiscal Year)',
        className: 'column-target',
        children: [
          {
            key: 'target',
            dataIndex: 'target',
            className: 'column-target-year',
            width: 225,
          },
        ],
      },
      {
        title: 'Measures',
        key: 'measures',
        dataIndex: 'measures',
        className: 'column-measures',
        width: 145,
      },
    ],
  },
  {
    title: 'Allocated Budget (F-101)',
    className: 'column-allocated-budget',
    children: [
      {
        title: '(in Php \'000)',
        key: 'budget',
        dataIndex: 'budget',
        className: 'column-in-php',
        width: 150,
      },
    ],
  },
  {
    title: 'Targets Basis',
    key: 'targetsBasis',
    dataIndex: 'targetsBasis',
    className: 'column-targets-basis',
    width: 250,
  },
  {
    title: 'Cascading Level',
    key: 'cascadingLevel',
    dataIndex: 'cascadingLevel',
    className: 'column-cascading-level',
    width: 190,
  },
  {
    title: 'Office/s Accountable',
    className: 'column-office-accountable',
    children: [
      {
        title: 'Implementing Office',
        key: 'implementing',
        dataIndex: 'implementing',
        className: 'column-implementing',
        width: 350,
      },
      {
        title: 'Supporting Office',
        key: 'supporting',
        dataIndex: 'supporting',
        className: 'column-supporting',
        width: 350,
      },
    ],
  },
  {
    title: 'Other Remarks (MoV)',
    key: 'remarks',
    dataIndex: 'remarks',
    className: 'column-remarks',
    width: 200,
    ellipsis: true,
  },
  {
    title: 'Actions',
    key: 'operation',
    fixed: 'right',
    className: 'column-action',
    width: 100,
  },
]

export const formTemplateTableColumns = [
  {
    title: '#',
    key: 'count',
    dataIndex: 'count',
    className: 'column-count',
    width: 60,
  },
  {
    title: 'Sub Category',
    key: 'subCategory',
    dataIndex: 'subCategory',
    className: 'column-sub-category',
    width: 280,
  },
  {
    title: 'Performance Indicator',
    key: 'name',
    dataIndex: 'name',
    className: 'column-pi-name',
    width: 450,
  },
  {
    title: 'is Header PI?',
    key: 'isHeader',
    dataIndex: 'isHeader',
    className: 'column-is-header',
    width: 100,
  },
  {
    title: 'Success Indicators',
    className: 'column-success-indicator',
    width: 400,
    children: [
      {
        title: 'Target (per Fiscal Year)',
        className: 'column-target',
        children: [
          {
            key: 'target',
            dataIndex: 'target',
            className: 'column-target-year',
            width: 225,
          },
        ],
      },
      {
        title: 'Measures',
        key: 'measures',
        dataIndex: 'measures',
        className: 'column-measures',
        width: 145,
      },
    ],
  },
  {
    title: 'Actions',
    key: 'operation',
    fixed: 'right',
    className: 'column-action',
    width: 100,
  },
]

export const listTableColumns = [
  {
    title: 'Year',
    dataIndex: 'year',
    key: 'year',
    className: 'column-year',
    width: 60,
  },
  {
    title: 'Document Name',
    dataIndex: 'document_name',
    key: 'documentName',
    className: 'column-document-name',
    width: 250,
  },
  {
    title: 'Date Created',
    dataIndex: 'created_at',
    key: 'dateCreated',
    className: 'column-created-at',
    width: 150,
  },
  {
    title: 'Date Published',
    dataIndex: 'published_date',
    key: 'datePublished',
    className: 'column-published-date',
    width: 150,
  },
  {
    title: 'Status',
    dataIndex: 'is_active',
    key: 'status',
    className: 'column-status',
    width: 70,
  },
  {
    title: 'Action',
    dataIndex: 'operation',
    className: 'column-action',
    width: 250,
    key: 'operation',
  },
]
