import React, { Component } from 'react'

import { Pagination } from 'antd'


class TabsPage extends Component{
  constructor(props) {
    super(props)
  }
  render(){
    return(
      <div className="tabs_page">
         <Pagination showQuickJumper size="small" defaultCurrent={1} total={50}  />,
      </div>
    )
  }
}

export default TabsPage
