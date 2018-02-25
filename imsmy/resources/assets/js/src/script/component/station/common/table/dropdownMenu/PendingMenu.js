import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'

import ModalTransfer from '../../../../common/modal/ModalTransfer'
import ModalScreen from '../../../../common/modal/ModalScreen'

//下拉菜单
class PendingMenu extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false
     }
  }
  render() {
    const tableID = this.props.operation? this.props.record : this.props.SelectAll

    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          <Menu.Item  key="1">
            <ModalTransfer id={tableID} visible={this.state.visible} />
          </Menu.Item>
          <Menu.Item  key="2" >
            <ModalScreen id={tableID} visible={this.state.visible} />
          </Menu.Item>
        </Menu>}
        trigger={['click']}>
              <Button type={this.props.operation ? "" : "primary"} >
                  {this.props.operation ? "操作" : "批量操作"}
                    <Icon type="down" />
              </Button>
      </Dropdown>
      )
  }

}

export default PendingMenu
